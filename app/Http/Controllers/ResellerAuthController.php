<?php

namespace App\Http\Controllers;

use App\Models\ResellerApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ResellerAuthController extends Controller
{
    /**
     * Show reseller login page or set-password page when token is present.
     */
    public function showLoginPage(Request $request)
    {
        $token = $request->query('token');

        if ($token) {
            return $this->showSetPasswordPage($token);
        }

        return view('auth.reseller-login', [
            'error' => $request->session()->get('error'),
        ]);
    }

    /**
     * Validate token and show Set Password form.
     */
    protected function showSetPasswordPage(string $token)
    {
        $app = ResellerApplication::where('activation_token', $token)
            ->where('activation_token_expires_at', '>', now())
            ->where('status', ResellerApplication::STATUS_APPROVED)
            ->first();

        if (!$app) {
            return redirect('/reseller-login')
                ->with('error', 'Invalid or expired link. Please contact your administrator.');
        }

        $user = User::where('email', $app->email)->first();
        if (!$user) {
            return redirect('/reseller-login')
                ->with('error', 'Account not found. Please contact your administrator.');
        }

        return view('auth.reseller-set-password', [
            'token' => $token,
            'email' => $app->email,
            'name' => $app->full_name,
        ]);
    }

    /**
     * Set password and log reseller in.
     */
    public function setPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $app = ResellerApplication::where('activation_token', $request->token)
            ->where('activation_token_expires_at', '>', now())
            ->where('status', ResellerApplication::STATUS_APPROVED)
            ->first();

        if (!$app) {
            return redirect('/reseller-login')
                ->with('error', 'Invalid or expired link. Please contact your administrator.');
        }

        $user = User::where('email', $app->email)->first();
        if (!$user) {
            return redirect('/reseller-login')
                ->with('error', 'Account not found. Please contact your administrator.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        $app->update([
            'activation_token' => null,
            'activation_token_expires_at' => null,
        ]);

        Auth::login($user, $request->boolean('remember'));

        return redirect()->route('sellar.dashboard')->with('status', true)->with('message', 'Password set successfully. Welcome to your Reseller Portal!');
    }
}
