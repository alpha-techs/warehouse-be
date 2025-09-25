<?php

namespace App\Support;

class DocumentStorage
{
    public const LOCAL = 'local';
    public const S3 = 's3';

    public static function defaultType(): string
    {
        $default = config('document_storage.default', self::LOCAL);

        return in_array($default, [self::LOCAL, self::S3], true)
            ? $default
            : self::LOCAL;
    }

    public static function disk(?string $storageType): string
    {
        $type = $storageType ?: self::defaultType();
        $disk = config("document_storage.disk_map.{$type}");

        if (is_string($disk) && $disk !== '') {
            return $disk;
        }

        return config('filesystems.default', 'public');
    }

    public static function temporaryUrlTtl(): int
    {
        return (int) config('document_storage.s3.temporary_url_ttl', 3600);
    }
}
