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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->index()->comment('发票ID');
            $table->unsignedBigInteger('outbound_id')->nullable()->comment('出库单ID');
            $table->unsignedBigInteger('outbound_item_id')->nullable()->comment('出库明细ID');
            $table->string('outbound_order_id')->nullable()->comment('出库订单编号');
            $table->date('outbound_date')->nullable()->comment('出库日期');
            $table->unsignedBigInteger('product_id')->nullable()->comment('商品ID');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->integer('quantity')->default(0)->comment('数量');
            $table->string('currency')->default('JPY')->comment('币种');
            $table->unsignedInteger('unit_price')->default(0)->comment('单价');
            $table->unsignedInteger('line_amount')->default(0)->comment('行金额');
            $table->unsignedInteger('tax_amount')->default(0)->comment('税额');
            $table->string('note', 1024)->nullable()->comment('备注');
            $table->timestamps();

            $table->index(['outbound_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
