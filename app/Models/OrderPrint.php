<?php

namespace App\Models;

use App\Support\DocumentStorage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property string $id
 * @property int $order_id
 * @property string $format
 * @property string $status
 * @property string $storage
 * @property string|null $file_path
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $expires_at
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
