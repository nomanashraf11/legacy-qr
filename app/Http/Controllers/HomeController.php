<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class HomeController extends Controller
{
    // public function emailVer()
    // {
    //     try {
    //         auth()->user()->sendEmailVerificationNotification();
    //         return view('auth.verify-email');
    //     } catch (\Throwable $th) {
    //         dd($th->getMessage());
    //     }
    // }
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        if (Auth::user()->hasRole('admin')) {
            return redirect(route('dashboard'))->with('verified', true);
        }
        if (Auth::user()->hasRole('re-sellers')) {
            return redirect(route('sellar.dashboard'))->with('verified', true);
        }
    }
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            if (Auth::user()->hasRole('admin')) {
                return redirect(route('dashboard'))->with('verified', true);
            }
            if (Auth::user()->hasRole('re-sellers')) {
                return redirect(route('sellar.dashboard'))->with('verified', true);
            }
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('resent', true);
    }
}
