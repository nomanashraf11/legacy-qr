<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\ReSeller;
use App\Models\LocalUser;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Database\Seeders\LinkSeeder;
use Database\Seeders\BatchSeeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
        $admin = User::create([
            'name' => 'Test User',
            'email' => 'emannadeem0730@gmail.com',
            'password' => Hash::make('password'),
            'email_verified_at' => Carbon::now(),
        ]);
        $admin->assignRole('admin');
        $admin->localUser()->create([
            'uuid' => Str::uuid(),
            'phone' => '03367696680',
            'user_id' => $admin->id,
        ]);
        Admin::create([
            'qr_price' => 10,
            'min_quantity' => 10,
            'max_quantity' => 100,
            'facebook' => 'https://www.facebook.com/',
            'instagram' => 'https://www.instagram.com/',
            'amazon' => 'https://www.amazon.com/',
            'reviews_link' => 'https://www.amazon.com/',
            'user_id' => $admin->id,
        ]);

        $this->call([
            BatchSeeder::class,
            UserSeeder::class,
            LinkSeeder::class,
        ]);
        // $seller = User::create([
        //     'name' => 'Re-Seller',
        //     'email' => 'eman.nadeem1999@gmail.com',
        //     'password' => Hash::make('password'),
        //     'email_verified_at' => Carbon::now(),
        // ]);
        // $seller->assignRole('re-sellers');
        // ReSeller::create([
        //     'uuid' => Str::uuid(),
        //     'phone' => '03367696680',
        //     'website' => 'https://www.youtube.com',
        //     'shipping_address' => 'Meer Market Jinnah Road',
        //     'user_id' => $seller->id,
        // ]);
        // $local_user = User::create([
        //     'name' => 'local_user',
        //     'email' => '181370023@gift.edu.pk',
        //     'password' => Hash::make('password'),
        //     'email_verified_at' => Carbon::now(),
        // ]);
        // $local_user->assignRole('local_user');
        // LocalUser::create([
        //     'uuid' => Str::uuid(),
        //     'phone' => '03367696680',
        //     'user_id' => $local_user->id,
        // ]);
    }
}
