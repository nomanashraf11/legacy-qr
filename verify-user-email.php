<?php
/**
 * Script to manually verify a user's email address
 * Usage: php verify-user-email.php <email>
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Carbon\Carbon;

// Get email from command line argument or use default
$email = $argv[1] ?? 'naumanashraf3000@gmail.com';

echo "🔍 Looking for user with email: {$email}\n\n";

$user = User::where('email', $email)->first();

if (!$user) {
    echo "❌ User not found: {$email}\n";
    echo "\nAvailable users:\n";
    $allUsers = User::select('id', 'name', 'email', 'email_verified_at')->get();
    foreach ($allUsers as $u) {
        $verified = $u->email_verified_at ? '✅ Verified' : '❌ Not Verified';
        echo "  - {$u->email} ({$u->name}) - {$verified}\n";
    }
    exit(1);
}

echo "✅ User found:\n";
echo "   ID: {$user->id}\n";
echo "   Name: {$user->name}\n";
echo "   Email: {$user->email}\n";
echo "   Current Status: " . ($user->email_verified_at ? '✅ Verified' : '❌ Not Verified') . "\n";

if ($user->email_verified_at) {
    echo "\n⚠️  Email is already verified at: {$user->email_verified_at}\n";
    echo "Do you want to update it anyway? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    if (strtolower($line) !== 'y') {
        echo "Cancelled.\n";
        exit(0);
    }
}

// Verify the email
$user->email_verified_at = Carbon::now();
$user->save();

echo "\n✅ Email verified successfully!\n";
echo "   Verified at: {$user->email_verified_at}\n";
echo "\n🎉 User can now login!\n";
