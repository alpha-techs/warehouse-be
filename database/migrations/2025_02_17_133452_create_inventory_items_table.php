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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id')->nullable()->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('inbound_order_id')->nullable()->comment('入库订单ID');
            $table->unsignedBigInteger('inbound_id')->index()->comment('入库ID');
            $table->unsignedBigInteger('inbound_item_id')->index()->comment('入库商品ID');
            $table->date('inbound_date')->nullable()->comment('入库日期');
            $table->unsignedBigInteger('product_id')->nullable()->comment('商品ID');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->string('per_item_weight', 20)->nullable()->comment('单件重量');
            $table->string('per_item_weight_unit', 20)->nullable()->comment('单件重量单位');
            $table->string('total_weight', 20)->nullable()->comment('总重量');
            $table->date('manufacture_date')->nullable()->comment('生产日期');
            $table->date('best_before_date')->nullable()->comment('最佳使用日期');
            $table->string('lot_number')->nullable()->comment('批次号');
            $table->string('ship_name')->nullable()->comment('船名');
            $table->integer('inbound_quantity')->nullable()->comment('入库数量');
            $table->integer('left_quantity')->nullable()->comment('剩余数量');
            $table->integer('left_sub_quantity')->nullable()->comment('剩余数量');
            $table->boolean('muted')->default(false)->comment('是否静音');

            $table->softDeletes();
            $table->timestamps();

            $table->index(['warehouse_id', 'customer_id', 'product_id']);
            $table->index(['warehouse_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
