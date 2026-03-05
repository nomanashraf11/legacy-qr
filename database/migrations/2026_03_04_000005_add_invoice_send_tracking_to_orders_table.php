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
            $table->dateTime('invoice_sent_at')->nullable()->after('invoice_due_at');
            $table->string('invoice_send_status')->nullable()->after('invoice_sent_at');
            $table->text('invoice_send_error')->nullable()->after('invoice_send_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_sent_at', 'invoice_send_status', 'invoice_send_error']);
        });
    }
};
