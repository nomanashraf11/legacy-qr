<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_DELIVERED = 2;

    const CARRIERS = ['USPS', 'UPS', 'FedEx'];

    protected $fillable = [
        'uuid',
        'tracking_id',
        'tracking_details',
        'shipping_carrier',
        'qr_codes',
        'amount',
        'status',
        're_seller_id',
        'accepted_at',
        'shipping_email_sent',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'shipping_email_sent' => 'boolean',
    ];
    public function reSeller()
    {
        return $this->belongsTo(ReSeller::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
