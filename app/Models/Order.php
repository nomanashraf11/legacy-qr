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
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'stripe_customer_id',
        'stripe_payment_status',
        'stripe_shipping_name',
        'stripe_shipping_phone',
        'stripe_shipping_address',
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
        'stripe_shipping_address' => 'array',
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
