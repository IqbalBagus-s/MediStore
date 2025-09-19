<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('id');
            $table->enum('order_type', ['ambil_ditempat', 'kirim_paket']);
            $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'ditolak', 'dikirim', 'selesai']);
            $table->enum('payment_method', ['cash_on_delivery', 'online_payment']);
            $table->text('shipping_address')->nullable();
            $table->timestamps();
            $table->softDeletes('delete_at');
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
