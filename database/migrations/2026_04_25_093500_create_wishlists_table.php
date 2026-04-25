<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->uuid('buyer_id')->index();
            $table->uuid('product_id')->index();
            $table->timestamps();

            $table->unique(['buyer_id', 'product_id']);
            $table->foreign('buyer_id')->references('uuid')->on('buyers')->onDelete('cascade');
            $table->foreign('product_id')->references('uuid')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};