<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property int|null $warehouse_id 仓库ID
 * @property int|null $customer_id 客户ID
 * @property int|null $inbound_order_id 入库订单ID
 * @property string|null $inbound_date 入库日期
 * @property int|null $product_id 商品ID
 * @property string|null $per_item_weight 单件重量
 * @property string|null $per_item_weight_unit 单件重量单位
 * @property string|null $total_weight 总重量
 * @property string|null $manufacture_date 生产日期
 * @property string|null $best_before_date 最佳使用日期
 * @property string|null $lot_number 批次号
 * @property string|null $ship_name 船名
 * @property int|null $inbound_quantity 入库数量
 * @property int|null $left_quantity 剩余数量
 * @property int|null $left_sub_quantity 剩余数量
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereBestBeforeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereInboundDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereInboundOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereInboundQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereLeftQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereLeftSubQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereManufactureDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem wherePerItemWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem wherePerItemWeightUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereShipName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereTotalWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventoryItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class InventoryItem extends BaseModel
{
    use SoftDeletes;

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
