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
            $table->id();
            $table->unsignedBigInteger('order_id')->comment('注文ID');
            $table->unsignedBigInteger('product_id')->nullable()->comment('商品ID');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->string('product_sku')->nullable()->comment('商品SKU');
            $table->decimal('quantity', 12, 3)->default(0)->comment('数量');
            $table->string('unit', 64)->comment('计量单位');
            $table->unsignedInteger('unit_price')->default(0)->comment('单价（未含税）');
            $table->unsignedBigInteger('line_amount')->default(0)->comment('金额（未含税）');
            $table->string('currency', 16)->default('JPY')->comment('币种');
            $table->string('note', 1024)->nullable()->comment('备注');
            $table->timestamps();

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
