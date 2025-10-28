<?php

namespace App\Models;

use App\Support\DocumentStorage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $outbound_id 出库单ID
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
 * @property-read \App\Models\Outbound|null $outbound
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereOutboundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundReport whereWarehouseName($value)
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class OutboundReport extends BaseModel
{
    // 存储方式常量
    public const STORAGE_LOCAL = 'local';
    public const STORAGE_S3 = 's3';

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class, 'outbound_id', 'id');
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

    public static function defaultStorageType(): string
    {
        return DocumentStorage::defaultType() === DocumentStorage::S3
            ? self::STORAGE_S3
            : self::STORAGE_LOCAL;
    }

    public function getStorageDisk(): string
    {
        return DocumentStorage::disk($this->storage);
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
            $expiresAt = now()->addSeconds(DocumentStorage::temporaryUrlTtl());

            return Storage::disk($this->getStorageDisk())->temporaryUrl($this->file_path, $expiresAt);
        }

        return null;
    }
}
