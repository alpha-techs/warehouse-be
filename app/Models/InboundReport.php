<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $inbound_id 入库单ID
 * @property int $warehouse_id 仓库ID
 * @property string|null $warehouse_name 仓库名称
 * @property int|null $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property string $format 格式
 * @property string $status 状态
 * @property string $storage 存储类型
 * @property string|null $file_path 文件位置
 * @property string|null $error_message 错误信息
 * @property \Illuminate\Support\Carbon|null $started_at 开始生成时间
 * @property \Illuminate\Support\Carbon|null $completed_at 完成生成时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Inbound|null $inbound
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereInboundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundReport whereWarehouseName($value)
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class InboundReport extends BaseModel
{
    // 存储方式常量
    public const STORAGE_LOCAL = 'local';
    public const STORAGE_S3 = 's3';

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function inbound(): BelongsTo
    {
        return $this->belongsTo(Inbound::class, 'inbound_id', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markAsProcessing(): self
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        return $this;
    }

    public function markAsCompleted(string $filePath): self
    {
        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'completed_at' => now(),
        ]);

        return $this;
    }

    public function markAsFailed(string $errorMessage): self
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'completed_at' => now(),
        ]);

        return $this;
    }

    /**
     * 检查是否使用本地存储
     */
    public function isLocalStorage(): bool
    {
        return $this->storage === self::STORAGE_LOCAL;
    }

    /**
     * 检查是否使用S3存储
     */
    public function isS3Storage(): bool
    {
        return $this->storage === self::STORAGE_S3;
    }

    /**
     * 获取文件下载URL
     */
    public function getDownloadUrl(): ?string
    {
        if (!$this->file_path || !$this->isCompleted()) {
            return null;
        }

        if ($this->isLocalStorage()) {
            return url('storage/' . $this->file_path);
        }

        if ($this->isS3Storage()) {
            // 将来支持S3时的逻辑
            return \Storage::disk('s3')->url($this->file_path);
        }

        return null;
    }
}
