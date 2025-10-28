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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 64)->unique()->comment('注文编号');
            $table->string('status', 32)->default('draft')->index()->comment('注文状态');
            $table->unsignedBigInteger('customer_id')->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->date('delivery_due_date')->comment('纳期');
            $table->string('delivery_postal_code')->nullable()->comment('纳品邮编');
            $table->string('delivery_detail_address1')->nullable()->comment('纳品地址1');
            $table->string('delivery_detail_address2')->nullable()->comment('纳品地址2');
            $table->string('contact_name')->comment('负责人姓名');
            $table->string('contact_phone', 64)->comment('联系电话');
            $table->string('currency', 16)->default('JPY')->comment('币种');
            $table->unsignedBigInteger('total_amount')->default(0)->comment('合计金额（未含税）');
            $table->string('notes', 1024)->nullable()->comment('备注');
            $table->timestamp('cancelled_at')->nullable()->comment('取消时间');
            $table->softDeletes();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('delivery_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
