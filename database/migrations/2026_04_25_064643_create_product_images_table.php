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
    Schema::create('product_images', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('product_id')->index(); // Relasi ke produk
        $table->string('image'); // Path file gambar
        $table->boolean('is_thumbnail')->default(false);
        $table->timestamps();

        // Foreign Key ke tabel products
        $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};