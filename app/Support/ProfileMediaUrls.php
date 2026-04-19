<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class ProfileMediaUrls
{
    /** Public URL for an uploaded profile picture filename, or null if empty. */
    public static function profilePicture(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        if (str_starts_with($stored, 'stock/')) {
            return asset('images/stock-covers/'.substr($stored, strlen('stock/')));
        }

        try {
            return Storage::disk(config('filesystems.default'))->url('images/profile/profile_pictures/'.$stored);
        } catch (\Exception $e) {
            return asset('images/profile/profile_pictures/'.$stored);
        }
    }

    /** Public URL for cover (uploaded or stock). */
    public static function coverPicture(?string $stored): ?string
    {
        if ($stored === null || $stored === '') {
            return null;
        }
        if (str_starts_with($stored, 'stock/')) {
            return asset('images/stock-covers/'.substr($stored, strlen('stock/')));
        }

        try {
            return Storage::disk(config('filesystems.default'))->url('images/profile/cover_pictures/'.$stored);
        } catch (\Exception $e) {
            return asset('images/profile/cover_pictures/'.$stored);
        }
    }
}
