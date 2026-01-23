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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();

            $table->decimal('qr_price', 20, 10);
            $table->integer('min_quantity');
            $table->bigInteger('max_quantity');
            $table->string('amazon');
            $table->string('facebook');
            $table->string('instagram');
            $table->string('reviews_link')->nullable();
            $table->integer('purchase')->default(0);
            $table->text('analytics')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
