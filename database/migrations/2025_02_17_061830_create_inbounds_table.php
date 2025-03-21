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
        Schema::create('inbounds', function (Blueprint $table) {
            $table->id();
            $table->string('inbound_order_id')->nullable()->comment('客户订单ID');
            $table->date('inbound_date')->nullable()->comment('入库日期');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->unsignedBigInteger('customer_contact_id')->nullable()->comment('客户联系人ID');
            $table->string('status')->comment('入库状态');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inbounds');
    }
};
