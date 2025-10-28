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
        Schema::create('express_sample_shipment_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('express_sample_shipment_id')->comment('急送样品出库ID');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('format')->default('excel')->comment('报告格式');
            $table->string('status')->default('pending')->comment('报告状态');
            $table->string('storage')->default('local')->comment('存储类型');
            $table->string('file_path')->nullable()->comment('文件路径');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('expires_at')->nullable()->comment('下载链接过期时间');
            $table->timestamp('started_at')->nullable()->comment('开始生成时间');
            $table->timestamp('completed_at')->nullable()->comment('完成生成时间');
            $table->timestamps();

            $table->index('status');
            $table->index('warehouse_id');
            $table->index('customer_id');
            $table->index('express_sample_shipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_sample_shipment_reports');
    }
};
