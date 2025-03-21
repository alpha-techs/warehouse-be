<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string|null $inbound_order_id 客户订单ID
 * @property string|null $inbound_date 入库日期
 * @property int $warehouse_id 仓库ID
 * @property int|null $customer_id 客户ID
 * @property int|null $customer_contact_id 客户联系人ID
 * @property string $status 入库状态
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InboundItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereCustomerContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereInboundDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereInboundOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inbound withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Inbound extends BaseModel
{
    use SoftDeletes;

    public function items(): HasMany
    {
        return $this->hasMany(InboundItem::class, 'inbound_id', 'id');
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
