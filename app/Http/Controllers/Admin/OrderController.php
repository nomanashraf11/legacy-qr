<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderAcceptedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\ReSeller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    /**
     * Send order notification email. Logs config, attempt, and any failure.
     * Does not throw - API response is not broken by mail failures.
     */
    private function sendOrderMail(string $type, string $recipientEmail, $mailable): bool
    {
        try {
            $mailer = config('mail.default');
            $logContext = [
                'type' => $type,
                'recipient' => $recipientEmail,
                'mailer' => $mailer,
            ];
            if ($mailer === 'smtp') {
                $logContext['host'] = config('mail.mailers.smtp.host') ?? 'N/A';
                $logContext['port'] = config('mail.mailers.smtp.port') ?? 'N/A';
            }
            Log::info('Order email: attempting to send', $logContext);
            Mail::to($recipientEmail)->send($mailable);
            Log::info('Order email: sent successfully', ['type' => $type, 'recipient' => $recipientEmail]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Order email: failed to send', [
                'type' => $type,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Order::with(['reSeller.user', 'orderItems'])->orderBy('created_at', 'desc');
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('order_number', function ($row) {
                        return '<span class="text-muted small">#' . $row->id . '</span>';
                    })
                    ->addColumn('order_date', function ($row) {
                        return $row->created_at ? $row->created_at->format('M j, Y') : '—';
                    })
                    ->addColumn('action', function ($row) {
                        $viewOrder = route('orderDetails', $row->uuid);
                        $btn = '<span class="d-inline-block me-2 order-action-tip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="View order details"><a href="' . $viewOrder . '" class="text-body"><i class="mdi mdi-eye fs-4"></i></a></span>';
                        if (!$row->accepted_at) {
                            $btn .= '<span class="d-inline-block me-2 order-action-tip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Accept order and send confirmation email"><i class="acceptOrderButton uil uil-check-circle text-success fs-4" style="cursor:pointer;" id="' . $row->uuid . '"></i></span>';
                        }
                        if ($row->accepted_at && ($row->status == Order::STATUS_PENDING || $row->status == Order::STATUS_IN_PROGRESS)) {
                            $btn .= '<span class="d-inline-block me-2 order-action-tip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Add / Edit shipping & tracking (required before marking delivered)"><i class="changeTrackingDetails uil uil-package text-info fs-4" style="cursor:pointer;" id="' . $row->uuid . '"></i></span>';
                            if ($row->status == Order::STATUS_IN_PROGRESS) {
                                $btn .= '<span class="d-inline-block order-action-tip" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Mark as delivered"><i class="changeStatusButton uil uil-truck text-primary fs-4" style="cursor:pointer;" id="' . $row->uuid . '"></i></span>';
                            }
                        }
                        return $btn;
                    })
                    ->addColumn('name', function ($row) {
                        if ($row->reSeller && $row->reSeller->user) {
                            return $row->reSeller->user->name;
                        }
                        return "—";
                    })
                    ->addColumn('items', function ($row) {
                        if ($row->orderItems && $row->orderItems->isNotEmpty()) {
                            $total = $row->orderItems->sum('quantity');
                            return $total . ' item' . ($total > 1 ? 's' : '');
                        }
                        return $row->qr_codes . ' QR';
                    })
                    ->addColumn('amount_fmt', function ($row) {
                        return '$' . number_format($row->amount, 2);
                    })
                    ->addColumn('status', function ($row) {
                        if ($row->status == Order::STATUS_PENDING) {
                            return '<span class="badge bg-warning">Pending</span>';
                        }
                        if ($row->status == Order::STATUS_IN_PROGRESS) {
                            return '<span class="badge bg-info">In Progress</span>';
                        }
                        return '<span class="badge bg-success">Delivered</span>';
                    })
                    ->rawColumns(['order_number', 'order_date', 'name', 'items', 'amount_fmt', 'status', 'action'])
                    ->make(true);
            }

            return view('admin.pages.orderList');
        } catch (\Throwable $th) {
            Log::error('Order list failed', ['error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function acceptOrder($uuid)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('uuid', $uuid)->firstOrFail();
            if ($order->accepted_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Order already accepted',
                ]);
            }
            $order->update(['accepted_at' => now()]);
            DB::commit();

            $order->load(['orderItems.product', 'reSeller.user']);
            $data = [
                'userName' => $order->reSeller->user->name,
                'orderNumber' => $order->id,
                'invoiceUrl' => url()->route('order.invoice.view', $order->uuid),
                'portalUrl' => url()->route('reseller.invoices'),
                'order' => $order,
            ];
            $mailSent = $this->sendOrderMail('order_accepted', $order->reSeller->user->email, new OrderAcceptedMail($data));

            return response()->json([
                'status' => true,
                'message' => $mailSent ? 'Order accepted. Reseller notified.' : 'Order accepted. Notification email could not be sent (see logs).',
            ]);
        } catch (\Throwable $th) {
            Log::error('Order accept failed', ['uuid' => $uuid, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }

    public function markAsDelivered($uuid)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('uuid', $uuid)->firstOrFail();

            if (!$order->accepted_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please accept the order first.',
                ]);
            }
            if ($order->status != Order::STATUS_IN_PROGRESS) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please add shipping/tracking info first to move the order to In Progress.',
                ]);
            }

            $order->update(['status' => Order::STATUS_DELIVERED]);
            DB::commit();

            $order->load('reSeller.user');
            $recipientEmail = $order->reSeller && $order->reSeller->user
                ? $order->reSeller->user->email
                : null;
            $mailSent = false;
            if ($recipientEmail) {
                $data = [
                    'userName' => $order->reSeller->user->name,
                    'orderNumber' => $order->id,
                ];
                $mailSent = $this->sendOrderMail('order_delivered', $recipientEmail, new OrderDeliveredMail($data));
            } else {
                Log::warning('Order delivered: no reseller/user email found, skipping notification', ['order_uuid' => $uuid]);
            }

            return response()->json([
                'status' => true,
                'message' => $mailSent ? 'Marked as Delivered' : 'Marked as Delivered. Notification email could not be sent (see logs).',
            ]);
        } catch (\Throwable $th) {
            Log::error('Order mark as delivered failed', ['uuid' => $uuid, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function getTrackingDetails($uuid)
    {
        try {
            $order = Order::where('uuid', $uuid)->firstorfail();
            return $order;
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function updateTrackingDetails($uuid, Request $request)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('uuid', $uuid)->firstOrFail();

            if (!$order->accepted_at) {
                return response()->json([
                    'status' => false,
                    'message' => 'Please accept the order first before adding tracking.',
                ]);
            }

            $order->update([
                'tracking_id' => $request->tracking_id,
                'tracking_details' => $request->tracking_details,
                'shipping_carrier' => $request->shipping_carrier,
            ]);

            $shouldSendShippingEmail = false;
            $shippingEmailData = null;

            if (!empty($request->tracking_id)) {
                $order->update(['status' => Order::STATUS_IN_PROGRESS]);
                if (!$order->shipping_email_sent) {
                    $order->update(['shipping_email_sent' => true]);
                    $order->load('reSeller.user');
                    $shouldSendShippingEmail = true;
                    $shippingEmailData = [
                        'userName' => $order->reSeller->user->name,
                        'orderNumber' => $order->id,
                        'tracking' => $order->tracking_id,
                        'trackingDetails' => $order->tracking_details,
                        'shippingCarrier' => $order->shipping_carrier,
                    ];
                }
            }
            DB::commit();

            // Send shipping email AFTER commit so mail failure does not rollback DB
            if ($shouldSendShippingEmail && $shippingEmailData) {
                $this->sendOrderMail('order_shipped', $order->reSeller->user->email, new OrderShippedMail($shippingEmailData));
            }

            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully',
            ]);
        } catch (\Throwable $th) {
            Log::error('Order tracking update failed', ['uuid' => $uuid, 'error' => $th->getMessage(), 'trace' => $th->getTraceAsString()]);
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function orderByResellers($uuid)
    {
        try {
            $sellar = ReSeller::where('uuid', $uuid)->firstorfail();
            $orders = $sellar->orders()->paginate(8);
            return view('admin.pages.orderListSellers', compact('orders'));
        } catch (\Throwable $th) {
            return redirect()->back()->with([
                'status' => true,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function resendNet30Invoice($uuid)
    {
        try {
            $order = Order::with('reSeller.user')->where('uuid', $uuid)->firstOrFail();

            if ($order->payment_method !== 'net30_invoice' || empty($order->stripe_invoice_id)) {
                return response()->json([
                    'status' => false,
                    'message' => 'This order is not a Net 30 invoice order.',
                ], 422);
            }

            $stripeSecret = config('services.stripe.secret');
            if (empty($stripeSecret)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Stripe is not configured.',
                ], 422);
            }

            $stripe = new \Stripe\StripeClient($stripeSecret);
            $stripe->invoices->sendInvoice($order->stripe_invoice_id, []);

            $order->update([
                'invoice_sent_at' => now(),
                'invoice_send_status' => 'sent',
                'invoice_send_error' => null,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Net 30 invoice email resent successfully.',
            ]);
        } catch (\Throwable $e) {
            if (isset($order) && $order instanceof Order) {
                $order->update([
                    'invoice_send_status' => 'failed',
                    'invoice_send_error' => $e->getMessage(),
                ]);
            }

            Log::warning('Resend Net 30 invoice failed', [
                'order_uuid' => $uuid,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Could not resend invoice right now.',
            ], 500);
        }
    }
}
