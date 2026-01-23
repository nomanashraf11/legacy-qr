<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderDeliveredMail;
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
                $data = Order::all();
                return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('action', function ($row) {
                        $viewOrder = route('orderDetails', $row->uuid);

                        $btn = '<a href=' . $viewOrder . '><i class="mdi mdi-eye fs-3"></i></a> &nbsp;';

                        if ($row->status == 0) {
                            $btn .= '<i class="changeStatusButton uil uil-truck text-danger fs-3" style="cursor:pointer;" name="deleteManagerButton" id="' . $row->uuid . '"></i>';
                        }

                        return $btn;
                    })
                    ->addColumn('name', function ($row) {
                        if ($row->reSeller) {
                            return $row->reSeller->user->name;
                        }
                        return "";
                    })
                    ->addColumn('status', function ($row) {
                        if ($row->status == 0) {
                            return '<span class="badge bg-warning">Pending</span>';
                        }
                        return '<span class="badge bg-success">Delivered</span>';
                    })
                    ->rawColumns(['name', 'status', 'action'])
                    ->make(true);
            }

            return view('admin.pages.orderList');
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return redirect(route('admin.home'))->with(['status' => false, 'message' => 'Something went wrong']);
        }
    }
    public function markAsDelivered($uuid)
    {
        try {
            DB::beginTransaction();
            $order = Order::where('uuid', $uuid)->firstorfail();
            $order->update([
                'id' => $order->id,
                'status' => 1,
            ]);
            DB::commit();
            $data = [
                'tracking' => $order->tracking_id,
                'userName' => $order->reSeller->user->name,
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
            $order = Order::where('uuid', $uuid)->firstorfail();
            $order->update([
                'id' => $order->id,
                'tracking_id' => $request->tracking_id,
                'tracking_details' => $request->tracking_details,
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong'
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
