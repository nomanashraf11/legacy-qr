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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('name');
            $table->date('dob');
            $table->date('dod');
            $table->string('profile_picture');
            $table->string('cover_picture');
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('spotify')->nullable();
            $table->string('youtube')->nullable();
            $table->text('bio');
            $table->text('longitude')->nullable();
            $table->text('latitude')->nullable();

            $table->unsignedBigInteger('link_id');
            $table->foreign('link_id')->references('id')->on('links')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
