<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('stripe_checkout_session_id')->nullable()->unique()->after('tracking_details');
            $table->string('stripe_payment_intent_id')->nullable()->index()->after('stripe_checkout_session_id');
            $table->string('stripe_customer_id')->nullable()->after('stripe_payment_intent_id');
            $table->string('stripe_payment_status')->nullable()->after('stripe_customer_id');
            $table->string('stripe_shipping_name')->nullable()->after('stripe_payment_status');
            $table->string('stripe_shipping_phone')->nullable()->after('stripe_shipping_name');
            $table->json('stripe_shipping_address')->nullable()->after('stripe_shipping_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['stripe_checkout_session_id']);
            $table->dropIndex(['stripe_payment_intent_id']);
            $table->dropColumn([
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'stripe_customer_id',
                'stripe_payment_status',
                'stripe_shipping_name',
                'stripe_shipping_phone',
                'stripe_shipping_address',
            ]);
        });
    }
};
