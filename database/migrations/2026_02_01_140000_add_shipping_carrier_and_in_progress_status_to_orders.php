<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_carrier', 20)->nullable()->after('tracking_details');
        });

        // Migrate status: 0=pending, 1=in_progress, 2=delivered (was: 0=pending, 1=delivered)
        DB::table('orders')->where('status', 1)->update(['status' => 2]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_carrier');
        });

        // Revert status: 2 -> 1 for delivered
        DB::table('orders')->where('status', 2)->update(['status' => 1]);
    }
};
