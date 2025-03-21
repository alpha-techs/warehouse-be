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
        Schema::create('inbound_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('inbound_id')->index()->comment('入库单ID');
            $table->unsignedBiginteger('product_id')->comment('商品ID');
            $table->integer('quantity')->comment('入库数量');
            $table->decimal('per_item_weight')->nullable()->comment('单件重量');
            $table->string('per_item_weight_unit')->nullable()->comment('单件重量单位');
            $table->decimal('total_weight')->nullable()->comment('总重量');
            $table->date('manufacture_date')->nullable()->comment('生产日期');
            $table->date('best_before_date')->nullable()->comment('最佳使用日期');
            $table->string('lot_number')->nullable()->comment('批次号');
            $table->string('ship_name')->nullable()->comment('船名');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbound_items');
    }
};
