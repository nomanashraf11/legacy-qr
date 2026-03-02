<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$email = 'naumanashraf3000@gmail.com';

$user = User::where('email', $email)->first();

if ($user) {
    echo "User found!\n";
    echo "Name: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password Hash: " . substr($user->password, 0, 20) . "...\n";
    echo "\n";
    echo "To reset the password, run:\n";
    echo "php artisan tinker\n";
    echo "Then execute:\n";
    echo "\$user = App\Models\User::where('email', '{$email}')->first();\n";
    echo "\$user->password = Hash::make('your_new_password');\n";
    echo "\$user->save();\n";
} else {
    echo "User with email '{$email}' not found in database.\n";
    echo "\n";
    echo "All users in database:\n";
    $users = User::select('id', 'name', 'email')->get();
    foreach ($users as $u) {
        echo "- {$u->email} ({$u->name})\n";
    }
}
