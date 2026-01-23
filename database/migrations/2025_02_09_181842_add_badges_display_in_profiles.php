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
            $table->string('badge')->nullable(true);
            $table->string('spouse_badge')->nullable(true);
            $table->boolean('dark_theme')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('badge');
            $table->dropColumn('spouse_badge');
            $table->dropColumn('dark_theme');
        });
    }
};
