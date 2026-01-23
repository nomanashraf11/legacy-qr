<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class RedirectController extends Controller
{
    public function redirectAfterLogin()
    {
        try {
            if (Auth::user()->hasRole('admin')) {
                return redirect(route('admin.dashboard'))->with('verified', true);
            }
            if (Auth::user()->hasRole('re-sellers')) {
                if (Auth::user()->changed == 1) {
                    return redirect(route('sellar.dashboard'))->with('verified', true);
                } else {
                    return redirect(route('settings'))->with([
                        'status' => true,
                        'message' => 'Kindly Change your password'
                    ]);
                }
            }
            if (Auth::user()->hasRole('local_user')) {
                Session::flush();
                return redirect(route('login'))->with([
                    'status' => false,
                    'message' => 'You Cannot Login'
                ]);
            }
        } catch (\Throwable $th) {
            return redirect(route('login'))->with([
                'status' => false,
                'message' => 'Something went wrong'
            ]);
        }
    }

    public function home()
    {
        try {
            return view('profile.show');
            if (Auth::user()->hasRole('admin')) {
                return redirect(route('admin.dashboard'))->with('verified', true);
            }
            if (Auth::user()->hasRole('re-sellers')) {
                return redirect(route('sellar.dashboard'))->with('verified', true);
            }
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }
}
