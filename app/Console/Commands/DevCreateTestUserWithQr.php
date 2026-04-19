<?php

namespace App\Console\Commands;

use App\Models\Link;
use App\Models\LocalUser;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DevCreateTestUserWithQr extends Command
{
    protected $signature = 'dev:create-test-user
                            {--email= : Email (default: random @example.test)}
                            {--password=TestPass123! : Password}
                            {--name=Test User : Display name}';

    protected $description = 'Create a verified local_user and attach a new QR link (skips email; for local testing only).';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command is disabled in production.');

            return self::FAILURE;
        }

        if (! Role::where('name', 'local_user')->exists()) {
            $this->error('Role "local_user" is missing. Run: php artisan db:seed --class=RoleSeeder');

            return self::FAILURE;
        }

        $email = $this->option('email');
        if (! $email) {
            $email = 'tester_'.Str::lower(Str::random(10)).'@example.test';
        }

        $password = (string) $this->option('password');
        $name = (string) $this->option('name');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("User already exists: {$email}");

            return self::FAILURE;
        }

        $qrUuid = Str::random(12);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            $localUser = LocalUser::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
            ]);

            Link::create([
                'uuid' => $qrUuid,
                'image' => null,
                'is_sold' => false,
                'batch_id' => null,
                'local_user_id' => $localUser->id,
                'version_type' => 'full',
            ]);

            $user->assignRole('local_user');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $clientBase = rtrim((string) env('CLIENT_URL', 'http://127.0.0.1:3000'), '/');
        $legacyUrl = "{$clientBase}/{$qrUuid}";
        $viteHint = 'http://127.0.0.1:3000/'.$qrUuid;

        $this->info('Created verified user + linked QR (no email sent).');
        $this->newLine();
        $this->table(
            ['Item', 'Value'],
            [
                ['Email', $email],
                ['Password', $password],
                ['QR UUID (path)', $qrUuid],
                ['CLIENT_URL page', $legacyUrl],
                ['Vite dev (typical)', $viteHint],
                ['API sign-in', 'POST '.rtrim((string) env('APP_URL', 'http://127.0.0.1:8000'), '/').'/api/signin'],
            ]
        );
        $this->newLine();
        $this->comment('Sign in at the app with email/password. The QR is already linked to this account.');
        $this->warn('Do not use weak passwords on shared or production databases.');

        return self::SUCCESS;
    }
}
