<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResellerApplicationRequest;
use App\Mail\ThankyouMail;
use App\Models\ResellerApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResellerApplicationController extends Controller
{
    /**
     * Store a new reseller application.
     */
    public function store(ResellerApplicationRequest $request)
    {
        try {
            DB::beginTransaction();

            $existingUser = User::where('email', $request->email)->first();
            if ($existingUser) {
                return response()->json([
                    'status' => false,
                    'message' => 'This email already has an account. Try another.',
                ]);
            }

            $existing = ResellerApplication::where('email', $request->email)
                ->whereIn('status', [ResellerApplication::STATUS_PENDING])
                ->first();

            if ($existing) {
                return response()->json([
                    'status' => false,
                    'message' => 'You have already submitted an application. We will be in touch shortly.',
                ]);
            }

            ResellerApplication::create([
                'business_name' => $request->business_name,
                'business_category' => $request->business_category,
                'years_in_business' => $request->years_in_business ?: null,
                'street_address' => $request->street_address ?: null,
                'city' => $request->city ?: null,
                'state' => $request->state ?: null,
                'zip_code' => $request->zip_code ?: null,
                'business_phone' => $request->business_phone ?: null,
                'website' => $request->website ?: null,
                'full_name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone ?: null,
                'estimated_monthly_volume' => $request->estimated_monthly_volume ?: null,
                'hear_about_us' => $request->hear_about_us ?: null,
                'additional_notes' => $request->additional_notes ?: null,
            ]);

            $data = [
                'userName' => $request->full_name,
            ];
            Mail::to($request->email)->send(new ThankyouMail($data));

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Our admin will review your application and get back to you shortly.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            \Log::error('Reseller application error: ' . $th->getMessage(), [
                'exception' => $th,
                'trace' => $th->getTraceAsString(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. Please try again later.',
            ]);
        }
    }
}
