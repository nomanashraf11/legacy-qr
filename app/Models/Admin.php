<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;
    protected $fillable = [
        'purchase',
        'qr_price',
        'min_quantity',
        'max_quantity',
        'amazon',
        'instagram',
        'facebook',
        'reviews_link',
        'analytics',
        'tawk',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
