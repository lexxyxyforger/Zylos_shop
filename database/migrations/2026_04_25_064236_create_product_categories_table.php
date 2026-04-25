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
    Schema::create('product_categories', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('parent_id')->nullable()->index(); // Untuk sub-kategori
        $table->string('image')->nullable();
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('tagline')->nullable();
        $table->longText('description')->nullable();
        $table->timestamps();

        // Relasi ke dirinya sendiri (Self-referencing)
        $table->foreign('parent_id')->references('uuid')->on('product_categories')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};