<?php

namespace App\Models;

use App\Observers\NameChangeObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\NameChange
 *
 * @property int $id
 * @property string|null $name_change_order_id 名义变更订单ID
 * @property string|null $name_change_date 名义变更日期
 * @property int $warehouse_id 仓库ID
 * @property string|null $warehouse_name 仓库名称
 * @property int|null $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property string $status 名义变更状态
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NameChangeItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereCarrierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereNameChangeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereNameChangeOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange whereWarehouseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NameChange withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
#[ObservedBy(NameChangeObserver::class)]
class NameChange extends BaseModel
{
    use SoftDeletes;

    public function items(): HasMany
    {
        return $this->hasMany(NameChangeItem::class, 'name_change_id', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
