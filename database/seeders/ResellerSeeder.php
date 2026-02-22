<?php

namespace Database\Seeders;

use App\Models\ReSeller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ResellerSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'reseller@demo.com'],
            [
                'name' => 'Demo Reseller',
                'password' => Hash::make('Reseller@123'),
                'email_verified_at' => Carbon::now(),
            ]
        );

        if (!$user->hasRole('re-sellers')) {
            $user->assignRole('re-sellers');
        }

        ReSeller::updateOrCreate(
            ['user_id' => $user->id],
            [
                'uuid' => Str::uuid(),
                'phone' => '555-123-4567',
                'website' => 'https://example.com',
                'shipping_address' => '123 Demo Street, Demo City, DC 12345',
            ]
        );
    }
}
