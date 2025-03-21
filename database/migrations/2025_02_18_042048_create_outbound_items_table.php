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
        Schema::create('outbound_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('outbound_id')->index()->comment('出库单ID');
            $table->unsignedBiginteger('inbound_item_id')->index()->comment('入库商品ID');
            $table->unsignedBigInteger('inventory_item_id')->index()->comment('库存商品ID');
            $table->unsignedBigInteger('product_id')->comment('商品ID');
            $table->integer('quantity')->comment('出库数量');
            $table->string('lot_number')->nullable()->comment('批次号');
            $table->string('note', 1024)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_items');
    }
};
