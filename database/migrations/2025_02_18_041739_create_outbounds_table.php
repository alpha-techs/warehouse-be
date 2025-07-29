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
        Schema::create('outbounds', function (Blueprint $table) {
            $table->id();
            $table->string('outbound_order_id')->nullable()->comment('客户订单ID');
            $table->date('outbound_date')->nullable()->comment('出库日期');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('carrier_name')->nullable()->comment('承运商名称');
            $table->string('status')->comment('出库状态');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbounds');
    }
};
