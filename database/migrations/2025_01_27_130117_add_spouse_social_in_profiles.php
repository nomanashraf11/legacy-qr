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
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('spouse_facebook')->nullable();
            $table->string('spouse_instagram')->nullable();
            $table->string('spouse_twitter')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('spouse_facebook');
            $table->dropColumn('spouse_instagram');
            $table->dropColumn('spouse_twitter');
        });
    }
};
