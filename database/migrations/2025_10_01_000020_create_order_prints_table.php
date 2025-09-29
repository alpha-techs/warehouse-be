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
        Schema::create('order_prints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->comment('注文ID');
            $table->string('format', 32)->default('excel')->comment('打印文件格式');
            $table->string('status', 32)->default('pending')->comment('任务状态');
            $table->string('storage', 32)->default('local')->comment('存储类型');
            $table->string('file_path')->nullable()->comment('文件路径');
            $table->text('error_message')->nullable()->comment('错误信息');
            $table->timestamp('started_at')->nullable()->comment('开始时间');
            $table->timestamp('completed_at')->nullable()->comment('完成时间');
            $table->timestamp('expires_at')->nullable()->comment('过期时间');
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_prints');
    }
};
