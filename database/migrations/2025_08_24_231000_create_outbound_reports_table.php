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
        Schema::create('outbound_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outbound_id')->comment('出库单ID');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('format')->default('pdf')->comment('格式');
            $table->string('status')->default('pending')->comment('状态');
            $table->string('storage')->default('local')->comment('存储类型');
            $table->string('file_path')->nullable()->comment('文件位置');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始生成时间');
            $table->timestamp('completed_at')->nullable()->comment('完成生成时间');
            $table->timestamps();

            // 添加外键约束
            $table->foreign('outbound_id')->references('id')->on('outbounds')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outbound_reports');
    }
};
