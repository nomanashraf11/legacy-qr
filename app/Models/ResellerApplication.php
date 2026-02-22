<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'business_category',
        'years_in_business',
        'street_address',
        'city',
        'state',
        'zip_code',
        'business_phone',
        'website',
        'full_name',
        'email',
        'phone',
        'estimated_monthly_volume',
        'hear_about_us',
        'additional_notes',
        'status',
        'activation_token',
        'activation_token_expires_at',
    ];

    protected $casts = [
        'years_in_business' => 'integer',
        'activation_token_expires_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
