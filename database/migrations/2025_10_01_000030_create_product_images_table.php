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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('media_id', 26);
            $table->unsignedInteger('order')->nullable()->comment('排序');
            $table->string('alt_text')->nullable()->comment('图片替代文本');
            $table->timestamps();

            $table->index('product_id');
            $table->index('media_id');
            $table->unique(['product_id', 'media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
