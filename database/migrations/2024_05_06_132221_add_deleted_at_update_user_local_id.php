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
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['local_user_id']); // Adjust this if your key has a specific name

            // Add the new foreign key with onDelete set to 'SET NULL'
            $table->foreign('local_user_id')
                ->references('id')
                ->on('local_users')
                ->onUpdate('cascade')
                ->onDelete('set null');
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('links', function (Blueprint $table) {
            $table->dropForeign(['local_user_id']); // Adjust this if your key has a specific name

            // Re-add the old foreign key with onDelete set to 'CASCADE'
            $table->foreign('local_user_id')
                ->references('id')
                ->on('local_users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->dropSoftDeletes();
        });
    }
};
