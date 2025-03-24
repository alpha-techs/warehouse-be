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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('商品名称');
            $table->string('sku')->nullable()->comment('商品编码');
            $table->unsignedBigInteger('leaf_category_id')->nullable()->comment('最小商品种类ID');
            $table->string('cargo_mark')->nullable()->comment('货品标记');
            $table->string('dimension_description')->nullable()->comment('尺寸描述');
            $table->decimal('length')->nullable()->comment('长度');
            $table->decimal('width')->nullable()->comment('宽度');
            $table->decimal('height')->nullable()->comment('高度');
            $table->decimal('weight')->nullable()->comment('重量');
            $table->enum('length_unit', ['cm', 'm'])->nullable()->comment('长度单位');
            $table->enum('weight_unit', ['kg', 'g'])->nullable()->comment('重量单位');
            $table->boolean('has_sub_package')->default(false)->comment('是否存在子包装');
            $table->string('sub_package_description')->nullable()->comment('子包装描述');
            $table->unsignedBigInteger('sub_package_count')->nullable()->comment('子包装数量');
            $table->boolean('is_fixed_weight')->nullable()->comment('是否固定重量');
            $table->boolean('is_active')->default(true)->comment('是否启用');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
