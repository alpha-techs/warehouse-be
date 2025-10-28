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
        Schema::create('express_sample_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('express_sample_order_id')->nullable()->comment('急送样品订单编号');
            $table->string('status')->default('draft')->comment('急送样品出库状态');
            $table->date('requested_ship_date')->nullable()->comment('希望出库日期');
            $table->date('desired_delivery_date')->nullable()->comment('希望送达日期');
            $table->string('desired_delivery_time_window')->nullable()->comment('希望送达时间段');
            $table->unsignedBigInteger('warehouse_id')->comment('仓库ID');
            $table->string('warehouse_name')->nullable()->comment('仓库名称');
            $table->unsignedBigInteger('customer_id')->nullable()->comment('客户ID');
            $table->string('customer_name')->nullable()->comment('客户名称');
            $table->unsignedBigInteger('customer_contact_id')->nullable()->comment('客户联系人ID');
            $table->string('delivery_service')->default('regular')->comment('宅急便类型');
            $table->unsignedInteger('package_count')->default(1)->comment('包裹箱数');
            $table->string('package_type')->nullable()->comment('包装类型说明');
            $table->string('delivery_fee_payer')->nullable()->comment('运费承担方');
            $table->text('sample_purpose')->nullable()->comment('样品发送目的');
            $table->string('recipient_company_name')->nullable()->comment('收件公司名称');
            $table->string('recipient_department')->nullable()->comment('收件部门');
            $table->string('recipient_name')->nullable()->comment('收件人姓名');
            $table->string('recipient_phone_number')->nullable()->comment('收件人电话');
            $table->string('recipient_postal_code')->nullable()->comment('邮政编码');
            $table->string('recipient_prefecture')->nullable()->comment('都道府县');
            $table->string('recipient_city')->nullable()->comment('市区町村');
            $table->string('recipient_address_line1')->nullable()->comment('地址行1');
            $table->string('recipient_address_line2')->nullable()->comment('地址行2');
            $table->string('emergency_contact_name')->nullable()->comment('紧急联系人姓名');
            $table->string('emergency_contact_phone_number')->nullable()->comment('紧急联系电话');
            $table->string('carrier_name')->nullable()->comment('承运商名称');
            $table->string('tracking_number')->nullable()->comment('追踪编号');
            $table->text('note')->nullable()->comment('备注');
            $table->timestamp('dispatched_at')->nullable()->comment('实际发货时间');
            $table->timestamp('delivered_at')->nullable()->comment('实际送达时间');
            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('desired_delivery_date');
            $table->index('warehouse_id');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('express_sample_shipments');
    }
};

