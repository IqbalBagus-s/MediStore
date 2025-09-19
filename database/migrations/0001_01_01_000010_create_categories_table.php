<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('category_id');
            $table->string('category_name');
            $table->timestamps();
            $table->softDeletes('delete_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
