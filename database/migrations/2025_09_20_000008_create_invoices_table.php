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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id'); // PK dengan Auto Increment
            $table->foreignId('order_id')->constrained('orders', 'order_id')->onDelete('cascade');
            $table->string('invoice_number')->unique(); // Format: INV-2025-0001
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at untuk soft delete
            
            // Index untuk optimasi query
            $table->index('order_id');
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};