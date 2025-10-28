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
        Schema::create('name_change_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('name_change_id')->comment('名义变更单ID');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->string('format')->comment('格式');
            $table->string('status')->comment('状态');
            $table->string('storage')->comment('存储类型');
            $table->string('file_path')->nullable()->comment('文件位置');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始生成时间');
            $table->timestamp('completed_at')->nullable()->comment('完成生成时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('name_change_reports');
    }
};
