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
            $table->string('payment_method')->nullable()->after('stripe_shipping_address');
            $table->string('stripe_invoice_id')->nullable()->unique()->after('payment_method');
            $table->string('stripe_invoice_number')->nullable()->after('stripe_invoice_id');
            $table->string('stripe_invoice_status')->nullable()->after('stripe_invoice_number');
            $table->unsignedSmallInteger('payment_terms_days')->nullable()->after('stripe_invoice_status');
            $table->dateTime('invoice_due_at')->nullable()->after('payment_terms_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['stripe_invoice_id']);
            $table->dropColumn([
                'payment_method',
                'stripe_invoice_id',
                'stripe_invoice_number',
                'stripe_invoice_status',
                'payment_terms_days',
                'invoice_due_at',
            ]);
        });
    }
};
