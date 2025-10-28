<?php

namespace App\Models;

use App\Contracts\Models\NameChangeStatus;
use App\Observers\NameChangeItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\NameChangeItem
 *
 * @property int $id
 * @property int $name_change_id 名义变更单ID
 * @property string|null $name_change_date 名义变更日期
 * @property NameChangeStatus|null $name_change_status 名义变更状态
 * @property int $inbound_item_id 入库商品ID
 * @property int $inventory_item_id 库存商品ID
 * @property int $warehouse_id 仓库ID
 * @property string|null $warehouse_name 仓库名称
 * @property int|null $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property int $product_id 商品ID
 * @property string|null $product_name 商品名称
 * @property int $quantity 名义变更数量
 * @property string|null $lot_number 批次号
 * @property string|null $note 备注
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InventoryItem|null $inventoryItem
 * @property-read \App\Models\NameChange|null $nameChange
 * @property-read \App\Models\Product|null $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereInboundItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereInventoryItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereLotNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereNameChangeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereNameChangeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereNameChangeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem whereWarehouseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChangeItem withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
#[ObservedBy(NameChangeItemObserver::class)]
class NameChangeItem extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        'name_change_status' => NameChangeStatus::class,
    ];

    public function nameChange(): BelongsTo
    {
        return $this->belongsTo(NameChange::class, 'name_change_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
