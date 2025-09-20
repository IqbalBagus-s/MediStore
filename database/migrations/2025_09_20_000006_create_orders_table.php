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
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id'); // PK dengan Auto Increment
            $table->foreignId('id')->constrained('users')->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->enum('order_type', ['ambil_ditempat', 'kirim_paket']);
            $table->enum('status', [
                'menunggu_persetujuan', 
                'disetujui', 
                'ditolak', 
                'dikirim', 
                'selesai'
            ])->default('menunggu_persetujuan');
            $table->enum('payment_method', ['cash_on_delivery', 'online_payment']);
            $table->enum('payment_status', ['pending', 'paid', 'canceled']);
            $table->text('shipping_address')->nullable(); // Nullable, untuk kirim_paket
            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at untuk soft delete
            
            // Index untuk optimasi query
            $table->index('id');
            $table->index('status');
            $table->index('order_type');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};