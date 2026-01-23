<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Mail\VerificationEmail;

class VerificationController extends Controller
{
    private function verifyOTP($email, $otp)
    {
        if (Cache::has('otp_' . $email)) {
            $storedOTP = Cache::get('otp_' . $email);

            return $storedOTP === $otp;
        }

        return false; // OTP not found or expired
    }
    public function generateOTP()
    {
        return Str::random(6); // You can adjust the OTP length as needed
    }

    public function sendOTP($email, $otp)
    {
        $url = env('CLIENT_URL') . '/verify-email?email=' . $email . '&otp=' . $otp;
        Mail::to($email)->send(new VerificationEmail($url));  // Create a Mailable for this
    }

    public function sendOTPRequest(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $email = $request->input('email');
            $otp = $this->generateOTP();

            $user = User::where('email', $email)->first();
            $user->otp = $otp;
            $user->save();

            $this->sendOTP($email, $otp);

            return response()->json(['status' => 200, 'message' => 'OTP sent successfully']);
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json(['status' => 500, 'message' => 'Internal server error']);
        }
    }
    public function verify(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'otp' => 'required',
            ]);

            // $isValidOTP = $this->verifyOTP($request->input('email'), $request->input('verification_otp'));
            $user = User::where('email', $request->input('email'))->where('otp', $request->input('otp'))->first();

            if ($user) {
                $user = User::where('email', $request->input('email'))->first();
                $user->email_verified_at = Carbon::now();
                $user->otp = null;
                $token = $user->createToken('Api Token')->plainTextToken;

                $user->save();
                return response()->json(['status' => 200, 'message' => 'Your Email has been Verified Successfully', 'token' => $token]);
            } else {
                return response()->json(['status' => 401, 'message' => 'OTP is not correct or already been verified.']);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
            return response()->json(['status' => 500, 'message' => 'Something went wrong']);
        }
    }
}
