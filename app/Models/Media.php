<?php

namespace App\Models;

use Illuminate\Support\Str;

/**
 * App\Models\Media
 *
 * @property string $id
 * @property string $collection 媒体分组
 * @property string $disk 存储磁盘
 * @property string $path 存储路径
 * @property string $file_name 原始文件名
 * @property string|null $content_type 文件类型
 * @property int $size 文件大小（字节）
 * @property string $visibility 可见性
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Media whereVisibility($value)
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Media extends BaseModel
{
    protected $table = 'media';

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            if (! $media->getKey()) {
                $media->setAttribute($media->getKeyName(), (string) Str::ulid());
            }
        });
    }
}
