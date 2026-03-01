<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('image')->nullable()->after('description');
        });

        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('min_quantity');
            $table->integer('max_quantity'); // use 999999 for "and above"
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->index(['product_id', 'min_quantity', 'max_quantity']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('image');
        });
        Schema::dropIfExists('product_price_tiers');
    }
};
