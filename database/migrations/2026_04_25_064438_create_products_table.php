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
    Schema::create('products', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('store_id')->index();
        $table->uuid('product_category_id')->index();
        $table->string('name');
        $table->string('slug')->unique();
        $table->longText('about');
        $table->enum('condition', ['new', 'used'])->default('new');
        $table->decimal('price', 15, 2);
        $table->integer('weight'); // Dalam gram
        $table->integer('stock');
        $table->timestamps();

        // Foreign Keys
        $table->foreign('store_id')->references('uuid')->on('stores')->onDelete('cascade');
        $table->foreign('product_category_id')->references('uuid')->on('product_categories')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};