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
        Schema::create('store_ballances', function (Blueprint $table) {
        $table->uuid('uuid')->primary(); // Tetap pakai 'uuid' sebagai primary key
        $table->uuid('store_id')->index(); // Relasi ke tabel stores
        $table->decimal('balance', 15, 2)->default(0); // Decimal 15 digit, 2 di belakang koma
        $table->timestamps();

        // Relasi Foreign Key ke tabel stores
        $table->foreign('store_id')->references('uuid')->on('stores')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_ballances');
    }
};