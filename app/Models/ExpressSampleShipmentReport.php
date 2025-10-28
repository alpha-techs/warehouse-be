<?php

namespace App\Models;

use App\Support\DocumentStorage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $express_sample_shipment_id
 * @property int $warehouse_id
 * @property string|null $warehouse_name
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property string $format
 * @property string $status
 * @property string $storage
 * @property string|null $file_path
 * @property string|null $error_message
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpressSampleShipment|null $expressSampleShipment
 * @property-read \App\Models\Warehouse|null $warehouse
 * @property-read \App\Models\Customer|null $customer
 */
class ExpressSampleShipmentReport extends BaseModel
{
    public const STORAGE_LOCAL = 'local';
    public const STORAGE_S3 = 's3';

    protected $casts = [
        'expires_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $hidden = [
        'deleted_at',
    ];

    public function expressSampleShipment(): BelongsTo
    {
        return $this->belongsTo(ExpressSampleShipment::class, 'express_sample_shipment_id', 'id');
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

    public function markAsCompleted(string $filePath, ?\DateTimeInterface $expiresAt = null): self
    {
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
        if (!$this->file_path || !$this->isCompleted()) {
            return null;
        }

        if ($this->isLocalStorage()) {
            return url('storage/' . $this->file_path);
        }

        if ($this->isS3Storage()) {
            $expiresAt = $this->expires_at ?? now()->addSeconds(DocumentStorage::temporaryUrlTtl());

            return Storage::disk($this->getStorageDisk())->temporaryUrl($this->file_path, $expiresAt);
        }

        return null;
    }
}

