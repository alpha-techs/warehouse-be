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
        Schema::create('name_changes', function (Blueprint $table) {
            $table->id();
            $table->string('name_change_order_id')->nullable()->comment('名义变更订单ID');
            $table->date('name_change_date')->nullable()->comment('名义变更日期');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('status')->comment('名义变更状态');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('name_changes');
    }
};
