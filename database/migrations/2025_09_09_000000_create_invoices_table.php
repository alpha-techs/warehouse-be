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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique()->comment('发票编号');
            $table->string('status')->index()->comment('发票状态');
            $table->unsignedBigInteger('outbound_id')->comment('出库单ID');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->date('due_date')->nullable()->comment('付款截止日期');
            $table->timestamp('issue_date')->nullable()->comment('下发时间');
            $table->string('currency')->default('JPY')->comment('币种');
            $table->unsignedBigInteger('subtotal_amount')->default(0)->comment('商品小计金额');
            $table->unsignedBigInteger('tax_amount')->default(0)->comment('税额');
            $table->unsignedBigInteger('total_amount')->default(0)->comment('总金额');
            $table->string('issue_message', 1024)->nullable()->comment('下发消息');
            $table->string('cancel_reason', 1024)->nullable()->comment('取消原因');
            $table->string('notes', 1024)->nullable()->comment('备注');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['customer_id', 'deleted_at']);
            $table->index('outbound_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
