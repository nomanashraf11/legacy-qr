<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'tracking_id',
        'tracking_details',
        'qr_codes',
        'amount',
        'status',
        're_seller_id',
    ];
    public function reSeller()
    {
        return $this->belongsTo(ReSeller::class);
    }
}
