<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->uuid('uuid')->change();
            $table->unique('uuid');
            $table->uuid('relation_id')->nullable();
            $table->foreign('relation_id')->references('uuid')->on('relations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('relations', function (Blueprint $table) {
            $table->dropForeign(['relation_id']);
            $table->dropColumn('relation_id');
            $table->dropUnique(['uuid']);
        });
    }
};