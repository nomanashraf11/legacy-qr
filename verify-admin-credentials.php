<?php
/**
 * Verify Admin Credentials
 * Run: php verify-admin-credentials.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Admin User Credentials ===\n\n";

$admin = User::role('admin')->first();

if ($admin) {
    echo "✅ Admin user found!\n\n";
    echo "Email: {$admin->email}\n";
    echo "Name: {$admin->name}\n";
    echo "Email Verified: " . ($admin->email_verified_at ? 'Yes' : 'No') . "\n";
    echo "User ID: {$admin->id}\n\n";
    
    // Test password
    $testPassword = 'password';
    if (Hash::check($testPassword, $admin->password)) {
        echo "✅ Password verified!\n\n";
        echo "=== LOGIN CREDENTIALS ===\n";
        echo "Email: {$admin->email}\n";
        echo "Password: {$testPassword}\n";
    } else {
        echo "⚠️  Default password doesn't match. Password may have been changed.\n";
    }
} else {
    echo "❌ No admin user found.\n";
}
