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
    Schema::create('transactions', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->string('code')->unique(); // Kode invoice, misal: INV-20260425-XXXX
        $table->uuid('buyer_id')->index();
        $table->uuid('store_id')->index();
        
        // Data Pengiriman
        $table->integer('address_id');
        $table->text('address');
        $table->string('city');
        $table->string('postal_code');
        $table->string('shipping'); // Kurir (JNE, J&T, dll)
        $table->string('shipping_type'); // Layanan (Reguler, YES, dll)
        $table->decimal('shipping_cost', 15, 2);
        $table->string('tracking_number')->nullable(); // Resi
        
        // Rincian Biaya
        $table->decimal('tax', 15, 2);
        $table->decimal('grand_total', 15, 2);
        
        $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
        $table->timestamps();

        // Foreign Keys
        $table->foreign('buyer_id')->references('uuid')->on('buyers')->onDelete('cascade');
        $table->foreign('store_id')->references('uuid')->on('stores')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};