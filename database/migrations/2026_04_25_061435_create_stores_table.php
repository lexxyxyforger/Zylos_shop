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
    Schema::create('stores', function (Blueprint $table) {
        $table->uuid('uuid')->primary(); // Tetap pakai uuid sebagai primary key
        $table->uuid('user_id')->index(); // FK ke users
        $table->string('name');
        $table->string('logo')->nullable();
        $table->text('about')->nullable();
        $table->string('phone')->nullable();
        $table->integer('address_id')->nullable(); // Sesuai gambar: int
        $table->string('city')->nullable();
        $table->text('address')->nullable();
        $table->string('postal_code')->nullable();
        $table->boolean('is_verified')->default(false); // Sesuai gambar: boolean
        $table->timestamps();

        // Relasi ke tabel users
        $table->foreign('user_id')->references('uuid')->on('users')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};