<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $inbound_id 入库单ID
 * @property int $product_id 商品ID
 * @property int $quantity 入库数量
 * @property string|null $per_item_weight 单件重量
 * @property string|null $per_item_weight_unit 单件重量单位
 * @property string|null $total_weight 总重量
 * @property string|null $manufacture_date 生产日期
 * @property string|null $best_before_date 最佳使用日期
 * @property string|null $lot_number 批次号
 * @property string|null $ship_name 船名
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereBestBeforeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereInboundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereManufactureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem wherePerItemWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem wherePerItemWeightUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereShipName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereTotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InboundItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class InboundItem extends BaseModel
{
    use softDeletes;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
