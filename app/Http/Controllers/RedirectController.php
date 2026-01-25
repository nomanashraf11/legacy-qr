<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectController extends Controller
{
    /**
     * Redirect user after login based on their role
     */
    public function redirectAfterLogin()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // Check user roles and redirect accordingly
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('re-sellers')) {
            return redirect()->route('sellar.dashboard');
        }

        if ($user->hasRole('local_user')) {
            return redirect('/home');
        }

        // Default redirect
        return redirect('/home');
    }

    /**
     * Home page for authenticated users
     */
    public function home()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        // Redirect based on role
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('re-sellers')) {
            return redirect()->route('sellar.dashboard');
        }

        // For local users, show home page or redirect to React app
        // You can customize this based on your needs
        return redirect(config('app.frontend_url', 'http://localhost:3000'));
    }
}
