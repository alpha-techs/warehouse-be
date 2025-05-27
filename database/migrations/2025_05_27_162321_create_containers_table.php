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
        Schema::create('containers', function (Blueprint $table) {
            $table->id();
            $table->string('container_number')->index()->comment('集装箱号');
            $table->string('shipping_line')->nullable()->comment('船公司');
            $table->string('vessel_name')->nullable()->comment('船名');
            $table->string('voyage_number')->nullable()->comment('航次号');
            $table->date('arrival_date')->nullable()->comment('到港日期');
            $table->date('clearance_date')->nullable()->comment('清关日期');
            $table->date('discharge_date')->nullable()->comment('卸货日期');
            $table->date('return_date')->nullable()->comment('空箱归还日期');
            $table->string('status')->comment('状态');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
