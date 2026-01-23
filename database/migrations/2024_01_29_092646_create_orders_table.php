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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid();

            $table->string('tracking_id')->nullable();
            $table->string('tracking_details')->nullable();

            $table->bigInteger('qr_codes');
            $table->decimal('amount', 20, 10);
            $table->boolean('status');

            $table->unsignedBigInteger('re_seller_id');
            $table->foreign('re_seller_id')->references('id')->on('re_sellers')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
