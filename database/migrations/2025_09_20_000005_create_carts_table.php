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
        Schema::create('cart', function (Blueprint $table) {
            $table->id('cart_id'); // PK dengan Auto Increment
            $table->foreignId('id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products', 'product_id')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->timestamps(); // created_at & updated_at
            
            // Index untuk optimasi query
            $table->index(['id', 'product_id']);
            
            // Unique constraint untuk mencegah duplikasi item yang sama di cart user yang sama
            $table->unique(['id', 'product_id'], 'unique_user_product_cart');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart');
    }
};