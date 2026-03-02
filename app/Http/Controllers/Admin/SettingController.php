<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\QrDataRequest;
use App\Http\Requests\ResellerSettingsRequest;
use App\Models\Admin;
use App\Models\ReSeller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function setting()
    {
        try {
            return view('admin.pages.settings');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            DB::beginTransaction();
            if (Hash::check($request->old_password, auth()->user()->password)) {
                auth()->user()->update([
                    'password' => Hash::make($request->new_password),
                    'changed' => 1
                ]);
                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Password changed successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect old password'
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Internal server error'
            ]);
        }
    }
    public function changeQrData(QrDataRequest $request)
    {
        try {
            DB::beginTransaction();
            $admin = Auth::user()->admin;

            if ($admin) {
                $admin->update([
                    'qr_price' => $request->qr_price,
                    'min_quantity' => $request->min_quantity,
                    'max_quantity' => $request->max_quantity,
                ]);
            } else {
                Auth::user()->admin()->create([
                    'qr_price' => $request->qr_price,
                    'min_quantity' => $request->min_quantity,
                    'max_quantity' => $request->max_quantity,
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully',
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
            ]);
        }
    }
    public function updateDetails(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $user->update([
                'name' => $request->name,
                'email' => $request->email,

            ]);
            $admin = $user->admin;
            if ($admin) {
                $admin->update([
                    'id' => $admin->id,
                    'amazon' => $request->amazon,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                    'reviews_link' => $request->reviews_link,
                    'analytics' => $request->analytics,
                ]);
            } else {
                Auth::user()->admin()->create([
                    'amazon' => $request->amazon,
                    'facebook' => $request->facebook,
                    'instagram' => $request->instagram,
                    'reviews_link' => $request->reviews_link,
                    'analytics' => $request->analytics,
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([

                'status' => false,
                'message' => $th->getMessage(),
            ]);
        }
    }
    public function sellerUpdateDetails(ResellerSettingsRequest $request)
    {
        try {
            DB::beginTransaction();
            $user = Auth::user();
            $reSeller = $user->reSeller;

            $shippingAddress = implode(', ', array_filter([
                $request->street_address,
                $request->city,
                trim($request->state . ' ' . $request->postal_code),
            ]));

            $user->update([
                'id' => $user->id,
                'name' => $request->name,
                'email' => $request->email,
            ]);
            if ($reSeller) {
                $reSeller->update([
                    'id' => $reSeller->id,
                    'phone' => $request->phone,
                    'shipping_address' => $shippingAddress,
                ]);
            } else {
                ReSeller::create([
                    'uuid' => Str::uuid(),
                    'phone' => $request->phone,
                    'shipping_address' => $shippingAddress,
                    'website' => '',
                    'user_id' => $user->id,
                ]);
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Updated Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ]);
        }
    }
    public function togglePurchase()
    {
        try {
            DB::beginTransaction();
            $admin = Auth::user()->admin;
            if ($admin->purchase == 0) {
                $admin->update([
                    'id' => $admin->id,
                    'purchase' => 1,
                ]);
            } else {
                $admin->update([
                    'id' => $admin->id,
                    'purchase' => 0,
                ]);
            }
            DB::commit();
            return redirect(route('admin.settings'))->with([
                'status' => true,
                'message' => 'Purchased turned toggled Successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect(route('admin.settings'))->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
    public function changeStatusOfTawkto()
    {
        try {
            DB::beginTransaction();
            $admin = Auth::user()->admin;
            $admin->update([
                'id' => $admin->id,
                'tawk' => $admin->tawk === 1 ? 0 : 1,
            ]);
            DB::commit();
            return redirect(route('admin.settings'))->with([
                'status' => true,
                'message' => 'Tawk Script Status changes'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect(route('admin.settings'))->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }
}
