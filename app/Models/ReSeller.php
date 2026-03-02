<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReSeller extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'website',
        'phone',
        'shipping_address',
        'user_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Parse shipping_address into components for form editing.
     * Handles formats like "123 Main St, City, ST 12345" or "Street, City, State ZIP".
     */
    public function getAddressParts(): array
    {
        $addr = trim($this->shipping_address ?? '');
        if (empty($addr)) {
            return ['street_address' => '', 'city' => '', 'state' => '', 'postal_code' => ''];
        }
        $parts = array_map('trim', explode(',', $addr));
        if (count($parts) === 1) {
            return ['street_address' => $addr, 'city' => '', 'state' => '', 'postal_code' => ''];
        }
        $street = $parts[0];
        $city = $parts[1] ?? '';
        $stateZip = $parts[2] ?? '';
        $state = $stateZip;
        $postal = '';
        if (preg_match('/^(.+)\s+([0-9A-Za-z\-\s]+)$/', trim($stateZip), $m)) {
            $state = trim($m[1]);
            $postal = trim($m[2]);
        }
        return [
            'street_address' => $street,
            'city' => $city,
            'state' => $state,
            'postal_code' => $postal,
        ];
    }
}
