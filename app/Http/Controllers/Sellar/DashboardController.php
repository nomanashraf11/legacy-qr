<?php

namespace App\Http\Controllers\Sellar;

use App\Http\Controllers\Controller;
use App\Mail\QrPurchaseMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $orders = Auth::user()->reSeller->orders;
        return view('admin.pages.dashboard', compact('orders'));
    }
    public function buy_qr_codes_page()
    {
        if (User::role('admin')->first()->admin->purchase == 1) {
            return redirect(route('sellar.dashboard'))->with([
                'status' => false,
                'message' => 'Contact your admin to turn purchase ON'
            ]);
        }
        if (Auth::user()->reSeller) {
            if (Auth::user()->reSeller->shipping_address && Auth::user()->reSeller->phone) {
                return view('admin.pages.buy');
            } else {
                return redirect(route('settings'))->with([
                    'status' => false,
                    'message' => 'Complete Your Profile First'
                ]);
            }
        } else {
            return redirect(route('settings'))->with([
                'status' => false,
                'message' => 'Complete Your Profile First'
            ]);
        }
    }
    public function stripe(): View
    {
        return view('stripe');
    }
    public function stripeCheckout(Request $request)
    {
        try {
            DB::beginTransaction();
            $number_of_qr_codes = $request->qr_codes;

            $max_qr_codes = User::role('admin')->first()->admin->max_quantity;
            $min_qr_codes = User::role('admin')->first()->admin->min_quantity;
            if ($number_of_qr_codes < $min_qr_codes || $number_of_qr_codes > $max_qr_codes) {
                return redirect(route('myOrders'))->with([
                    'status' => false,
                    'message' => 'Limit Not Matching'
                ]);
            }
            $price = User::role('admin')->first()->admin->qr_price;
            $amount = $number_of_qr_codes * $price;

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $redirectUrl = route('stripe.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}';


            $response = $stripe->checkout->sessions->create([
                'success_url' => $redirectUrl,

                'customer_email' => Auth::user()->email,

                'payment_method_types' => ['link', 'card'],

                'line_items' => [
                    [
                        'price_data' => [
                            'product_data' => [
                                'name' => 'QR Codes',
                            ],
                            'unit_amount' => 100 * $price,
                            'currency' => 'USD',
                        ],
                        'quantity' => $number_of_qr_codes,
                    ],
                ],

                'mode' => 'payment',
                'allow_promotion_codes' => true,
            ]);

            DB::commit();
            // return redirect($response['url'])->with(['order' => $data, 'remaining_dates' => $datesArray]);
            return redirect($response['url'])->with(['number_of_qr_codes' => $number_of_qr_codes, 'amount' => $amount]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function stripeCheckoutSuccess(Request $request)
    {
        try {
            DB::beginTransaction();
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $response = $stripe->checkout->sessions->retrieve($request->session_id);
            $amount = session('amount');
            $number_of_qr_codes = session('number_of_qr_codes');

            $order = Order::create([
                'uuid' => Str::uuid(),
                'qr_codes' => $number_of_qr_codes,
                'amount' => $amount,
                'status' => 0,
                're_seller_id' => Auth::user()->reSeller->id,
                'tracking_details' => 'Order is pending'
            ]);
            $data = [
                'userName' => Auth::user()->name,
                'qr_codes' => $order->qr_codes,
                'amount' => $order->amount,
            ];
            Mail::to(Auth::user()->email)->send(new QrPurchaseMail($data));
            DB::commit();
            return redirect(route('myOrders'))->with([
                'status' => true,
                'message' => 'Order Placed successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            dd($th->getMessage());
        }
    }
    public function myOrders(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Auth::user()->reSeller->orders;

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('name', function ($row) {

                        if ($row->reSellar) {
                            return  $row->reSellar->user->name;
                        }
                        return  "";
                    })

                    ->addColumn('status', function ($row) {

                        if ($row->status == 0) {
                            return '<span class="badge bg-warning">Pending</span>';
                        }
                        return '<span class="badge bg-success">Delivered</span>';
                    })
                    ->addColumn('action', function ($row) {
                        $viewOrder = route('orderDetails', $row->uuid);
                        return '<a href=' . $viewOrder . '>View</a>';
                    })

                    ->rawColumns(['name', 'status', 'action'])
                    ->make(true);
            }
            return view('admin.pages.reseller.orderList');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function orderDetails($uuid)
    {
        try {
            $order = Order::where('uuid', $uuid)->first();
            return view('admin.pages.orderDetails', compact('order'));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
