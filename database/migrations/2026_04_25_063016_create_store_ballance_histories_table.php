<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('store_ballance_histories', function (Blueprint $table) {
        $table->uuid('uuid')->primary();
        
        // Kita definisikan manual tanpa helper foreignUuid biar presisi
        $table->uuid('store_ballance_id'); 
        
        $table->enum('type', ['income', 'withdraw']);
        $table->integer('reference_id')->nullable();
        $table->string('reference_type')->nullable();
        $table->decimal('amount', 15, 2);
        $table->string('remarks')->nullable();
        $table->timestamps();

        // Kita pasang foreign key-nya di baris terpisah agar lebih stabil
        $table->foreign('store_ballance_id')
              ->references('uuid')
              ->on('store_ballances')
              ->onDelete('cascade');
    });
}
    public function down(): void
    {
        Schema::dropIfExists('store_ballance_histories');
    }
};