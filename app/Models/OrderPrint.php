<?php

namespace App\Models;

use App\Support\DocumentStorage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property int $order_id 注文ID
 * @property string $format 打印文件格式
 * @property string $status 任务状态
 * @property string $storage 存储类型
 * @property string|null $file_path 文件路径
 * @property string|null $error_message 错误信息
 * @property \Illuminate\Support\Carbon|null $started_at 开始时间
 * @property \Illuminate\Support\Carbon|null $completed_at 完成时间
 * @property \Illuminate\Support\Carbon|null $expires_at 过期时间
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Order|null $order
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereStorage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderPrint whereUpdatedAt($value)
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class OrderPrint extends BaseModel
{
    public const STORAGE_LOCAL = 'local';
    public const STORAGE_S3 = 's3';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function markAsProcessing(): self
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        return $this;
    }

    public function markAsCompleted(string $filePath, ?int $ttlSeconds = null): self
    {
        $expiresAt = now()->addSeconds($ttlSeconds ?? 604800);

        $this->update([
            'status' => 'completed',
            'file_path' => $filePath,
            'completed_at' => now(),
            'expires_at' => $expiresAt,
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

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isLocalStorage(): bool
    {
        return $this->storage === self::STORAGE_LOCAL;
    }

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

    public function getDownloadUrl(): ?string
    {
        if (! $this->isCompleted() || ! $this->file_path) {
            return null;
        }

        if ($this->isLocalStorage()) {
            return url('storage/' . $this->file_path);
        }

        if ($this->isS3Storage()) {
            $expiration = $this->expires_at ?? now()->addSeconds(DocumentStorage::temporaryUrlTtl());

            return Storage::disk($this->getStorageDisk())->temporaryUrl($this->file_path, $expiration);
        }

        return null;
    }
}
