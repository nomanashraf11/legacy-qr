<?php
/**
 * Clear Session / Logout
 * Run: php clear-session.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

echo "=== Clearing Session ===\n\n";

if (Auth::check()) {
    echo "User logged in: " . Auth::user()->email . "\n";
    Auth::logout();
    echo "✅ Logged out\n";
} else {
    echo "No user logged in\n";
}

Session::flush();
echo "✅ Session cleared\n\n";

echo "You can now access http://localhost:8000/login\n";
