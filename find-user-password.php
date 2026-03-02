<?php
/**
 * Find User Password
 * Run: php find-user-password.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'naumanashraf3000@gmail.com';

echo "=== Searching for user: {$email} ===\n\n";

$user = User::where('email', $email)->first();

if ($user) {
    echo "✅ User found!\n\n";
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Email Verified: " . ($user->email_verified_at ? 'Yes' : 'No') . "\n";
    echo "Created At: {$user->created_at}\n";
    echo "Password Hash: {$user->password}\n\n";
    
    // Check common passwords
    $commonPasswords = ['password', '123456', '12345678', 'password123', 'admin', 'test'];
    
    echo "=== Testing Common Passwords ===\n";
    $found = false;
    foreach ($commonPasswords as $testPassword) {
        if (Hash::check($testPassword, $user->password)) {
            echo "✅ Password found: {$testPassword}\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "❌ Password not found in common passwords list.\n";
        echo "⚠️  Password is hashed (bcrypt) and cannot be retrieved in plain text.\n";
        echo "   You can reset it using: php artisan tinker\n";
        echo "   Then run: \$user = User::where('email', '{$email}')->first();\n";
        echo "            \$user->password = Hash::make('newpassword');\n";
        echo "            \$user->save();\n";
    }
} else {
    echo "❌ User not found with email: {$email}\n";
}
