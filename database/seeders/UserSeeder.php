<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 100 users using factory
        User::factory(100)->create()->each(function ($user) {
            // Assign 'local_user' role to each user
            $user->assignRole('local_user');

            // Create associated localUser for each user
            $user->localUser()->create([
                'uuid' => Str::uuid(),
                'phone' => $this->generate11DigitPhoneNumber(),
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * Generates an 11-digit phone number.
     *
     * @return string
     */
    private function generate11DigitPhoneNumber(): string
    {
        $phoneNumber = '';

        // Generate an 11-digit phone number
        for ($i = 0; $i < 11; $i++) {
            $phoneNumber .= mt_rand(0, 9);
        }

        return $phoneNumber;
    }
}
