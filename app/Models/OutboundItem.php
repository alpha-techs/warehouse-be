<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property int $outbound_id 出库单ID
 * @property int $inbound_item_id 入库商品ID
 * @property int $inventory_item_id 库存商品ID
 * @property int $product_id 商品ID
 * @property int $quantity 出库数量
 * @property string|null $lot_number 批次号
 * @property string|null $note 备注
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InventoryItem|null $inventoryItem
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereInboundItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereOutboundId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OutboundItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class OutboundItem extends BaseModel
{
    use SoftDeletes;

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
