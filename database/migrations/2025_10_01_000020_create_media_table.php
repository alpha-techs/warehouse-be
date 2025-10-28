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
        Schema::create('media', function (Blueprint $table) {
            $table->string('id', 26)->primary();
            $table->string('collection')->default('default')->comment('媒体分组');
            $table->string('disk', 50)->comment('存储磁盘');
            $table->string('path')->comment('存储路径');
            $table->string('file_name')->comment('原始文件名');
            $table->string('content_type')->nullable()->comment('文件类型');
            $table->unsignedBigInteger('size')->default(0)->comment('文件大小（字节）');
            $table->string('visibility')->default('private')->comment('可见性');
            $table->timestamps();

            $table->index('collection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
