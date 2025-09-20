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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id'); // PK dengan Auto Increment
            $table->foreignId('order_id')->constrained('orders', 'order_id')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Harga disimpan agar tidak berubah
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at untuk soft delete
            
            // Index untuk optimasi query
            $table->index('order_id');
            $table->index('product_id');
            $table->index(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};