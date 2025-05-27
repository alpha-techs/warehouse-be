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
        Schema::create('container_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('container_id')->index()->comment('集装箱ID');
            $table->unsignedBigInteger('product_id')->index()->comment('商品ID');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->unsignedInteger('quantity')->default(0)->comment('数量');
            $table->date('manufacture_date')->nullable()->comment('生产日期');
            $table->date('best_before_date')->nullable()->comment('最佳风味期限');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_items');
    }
};
