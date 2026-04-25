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
    Schema::create('product_reviews', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('transaction_id')->index();
        $table->uuid('product_id')->index();
        
        // Pakai tinyInteger karena angkanya kecil (1-5), lebih hemat storage
        $table->tinyInteger('rating')->unsigned(); 
        
        $table->text('review')->nullable();
        $table->timestamps();

        // Foreign Keys
        $table->foreign('transaction_id')->references('uuid')->on('transactions')->onDelete('cascade');
        $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};