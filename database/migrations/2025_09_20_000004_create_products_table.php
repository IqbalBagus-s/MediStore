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
            $table->id('product_id'); // PK dengan Auto Increment
            $table->foreignId('category_id')->constrained('categories', 'category_id')->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->string('image')->nullable(); // Path gambar dari server
            $table->decimal('price', 10, 2); // Format: 99999999.99
            $table->integer('stock')->default(0); // Default 0
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at untuk soft delete
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