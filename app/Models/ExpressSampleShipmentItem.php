<?php

namespace App\Models;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Observers\ExpressSampleShipmentItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ExpressSampleShipmentItem
 *
 * @property int $id
 * @property int $express_sample_shipment_id 急送样品出库ID
 * @property ExpressSampleShipmentStatus|null $shipment_status 急送样品出库状态
 * @property int $inventory_item_id 库存商品ID
 * @property int|null $inbound_item_id 入库商品ID
 * @property int $product_id 商品ID
 * @property string|null $product_name 商品名称
 * @property int $quantity 发货数量
 * @property string|null $quantity_unit 数量单位
 * @property string|null $sample_packaging 样品包装说明
 * @property string|null $lot_number 批次号
 * @property string|null $inbound_no 入库编号
 * @property \Illuminate\Support\Carbon|null $inbound_date 入库日期
 * @property string|null $note 备注
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpressSampleShipment|null $expressSampleShipment
 * @property-read \App\Models\InboundItem|null $inboundItem
 * @property-read \App\Models\InventoryItem|null $inventoryItem
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereExpressSampleShipmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereInboundDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereInboundItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereInboundNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereQuantityUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereSamplePackaging($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereShipmentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
#[ObservedBy(ExpressSampleShipmentItemObserver::class)]
class ExpressSampleShipmentItem extends BaseModel
{
    use SoftDeletes;

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'shipment_status' => ExpressSampleShipmentStatus::class,
        'inbound_date' => 'date',
        'quantity' => 'integer',
    ];

    public function expressSampleShipment(): BelongsTo
    {
        return $this->belongsTo(ExpressSampleShipment::class, 'express_sample_shipment_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function inboundItem(): BelongsTo
    {
        return $this->belongsTo(InboundItem::class, 'inbound_item_id', 'id');
    }
}
