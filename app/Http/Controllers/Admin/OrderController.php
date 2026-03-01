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
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Order::with(['reSeller.user', 'orderItems'])->orderBy('created_at', 'desc');
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('order_number', function ($row) {
                        return '<span class="text-muted small">#' . substr($row->uuid, 0, 8) . '</span>';
                    })
                    ->addColumn('order_date', function ($row) {
                        return $row->created_at ? $row->created_at->format('M j, Y') : '—';
                    })
                    ->addColumn('action', function ($row) {
                        $viewOrder = route('orderDetails', $row->uuid);
                        $btn = '<a href="' . $viewOrder . '" class="me-2" title="View / Dispatch"><i class="mdi mdi-eye fs-4"></i></a>';
                        if ($row->status == Order::STATUS_PENDING || $row->status == Order::STATUS_IN_PROGRESS) {
                            $btn .= '<i class="changeStatusButton uil uil-truck text-primary fs-4" style="cursor:pointer;" title="Mark Delivered" id="' . $row->uuid . '"></i>';
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
            dd($th->getMessage());
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

            $data = [
                'userName' => $order->reSeller->user->name,
                'orderNumber' => substr($order->uuid, 0, 8),
                'invoiceUrl' => url()->route('order.invoice.view', $order->uuid),
                'portalUrl' => url()->route('reseller.invoices'),
            ];
            Mail::to($order->reSeller->user->email)->send(new OrderAcceptedMail($data));

            return response()->json([
                'status' => true,
                'message' => 'Order accepted. Reseller notified.',
            ]);
        } catch (\Throwable $th) {
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
            $order = Order::where('uuid', $uuid)->firstorfail();
            $order->update([
                'id' => $order->id,
                'status' => Order::STATUS_DELIVERED,
            ]);
            DB::commit();
            $data = [
                'tracking' => $order->tracking_id,
                'userName' => $order->reSeller->user->name,
                'orderNumber' => substr($order->uuid, 0, 8),
            ];
            Mail::to($order->reSeller->user->email)->send(new OrderDeliveredMail($data));
            return response()->json([
                'status' => true,
                'message' => 'Marked as Delivered'
            ]);
        } catch (\Throwable $th) {
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
            $order->update([
                'tracking_id' => $request->tracking_id,
                'tracking_details' => $request->tracking_details,
                'shipping_carrier' => $request->shipping_carrier,
            ]);

            // When tracking is added, set status to In Progress and send email
            if (!empty($request->tracking_id)) {
                $order->update(['status' => Order::STATUS_IN_PROGRESS]);
                if (!$order->shipping_email_sent) {
                    $order->update(['shipping_email_sent' => true]);
                    $data = [
                        'userName' => $order->reSeller->user->name,
                        'orderNumber' => substr($order->uuid, 0, 8),
                        'tracking' => $order->tracking_id,
                        'trackingDetails' => $order->tracking_details,
                        'shippingCarrier' => $order->shipping_carrier,
                    ];
                    Mail::to($order->reSeller->user->email)->send(new OrderShippedMail($data));
                }
            }
            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully',
            ]);
        } catch (\Throwable $th) {
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
}
