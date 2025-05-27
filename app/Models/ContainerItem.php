<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ContainerItem
 *
 * @property int $id
 * @property int $container_id 集装箱ID
 * @property int $product_id 商品ID
 * @property string|null $product_name 商品名称
 * @property int $quantity 数量
 * @property \Illuminate\Support\Carbon|null $manufacture_date 生产日期
 * @property \Illuminate\Support\Carbon|null $best_before_date 最佳风味期限
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Container|null $container
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereBestBeforeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereContainerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereManufactureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContainerItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class ContainerItem extends BaseModel
{
    use SoftDeletes, HasFactory;

    protected $casts = [
        'manufacture_date' => 'date',
        'best_before_date' => 'date',
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'container_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
