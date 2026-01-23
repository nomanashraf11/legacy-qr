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
        Schema::table('users', function (Blueprint $table) {
            // Make the 'password' column nullable
            $table->string('password')->nullable(true)->change();

            // Add the 'google_id' column as nullable
            $table->string('google_id')->nullable(true)->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert the 'password' column to not nullable
            $table->string('password')->nullable(false)->change();

            // Drop the 'google_id' column
            $table->dropColumn('google_id');
        });
    }
};
