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
    Schema::create('buyers', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('user_id')->index(); // Relasi ke tabel users
        $table->string('profile_picture')->nullable(); // Kita pakai string ya, bukan decimal
        $table->string('phone_number')->nullable();
        $table->timestamps();

        // Foreign Key ke tabel users
        $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buyers');
    }
};