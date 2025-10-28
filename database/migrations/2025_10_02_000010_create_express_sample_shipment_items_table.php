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
        Schema::create('express_sample_shipment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('express_sample_shipment_id')->index()->comment('急送样品出库ID');
            $table->string('shipment_status')->nullable()->comment('急送样品出库状态');
            $table->unsignedBigInteger('inventory_item_id')->comment('库存商品ID');
            $table->unsignedBigInteger('inbound_item_id')->nullable()->comment('入库商品ID');
            $table->unsignedBigInteger('product_id')->comment('商品ID');
            $table->string('product_name')->nullable()->comment('商品名称');
            $table->integer('quantity')->default(1)->comment('发货数量');
            $table->string('quantity_unit')->nullable()->comment('数量单位');
            $table->string('sample_packaging')->nullable()->comment('样品包装说明');
            $table->string('lot_number')->nullable()->comment('批次号');
            $table->string('inbound_no')->nullable()->comment('入库编号');
            $table->date('inbound_date')->nullable()->comment('入库日期');
            $table->string('note', 1024)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();

            $table->index('inventory_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_sample_shipment_items');
    }
};

