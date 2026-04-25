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
    Schema::create('withdrawals', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        $table->uuid('store_id')->index(); // Relasi ke toko yang narik duit
        $table->decimal('amount', 15, 2);
        $table->string('bank_account_name');
        $table->string('bank_account_number');
        $table->string('bank_name');
        $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
        $table->timestamps();

        // Foreign Key ke tabel stores
        $table->foreign('store_id')->references('uuid')->on('stores')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};