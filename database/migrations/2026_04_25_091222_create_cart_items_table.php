<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('cart_id')->index();
            $table->uuid('product_id')->index();
            $table->uuid('product_size_id')->nullable()->index();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->foreign('cart_id')->references('uuid')->on('carts')->onDelete('cascade');
            $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
            $table->foreign('product_size_id')->references('uuid')->on('product_sizes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};