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
    Schema::create('transaction_details', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('transaction_id')->index();
        $table->uuid('product_id')->index();
        $table->integer('qty');
        $table->decimal('subtotal', 15, 2);
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
        Schema::dropIfExists('transaction_details');
    }
};