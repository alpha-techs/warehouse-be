<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 
 *
 * @property int $id
 * @property string|null $outbound_order_id 客户订单ID
 * @property string|null $outbound_date 出库日期
 * @property int $warehouse_id 仓库ID
 * @property int|null $customer_id 客户ID
 * @property int|null $customer_contact_id 客户联系人ID
 * @property string|null $carrier_name 承运商名称
 * @property string $status 出库状态
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OutboundItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereCarrierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereCustomerContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereOutboundDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereOutboundOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Outbound withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Outbound extends BaseModel
{
    use SoftDeletes;

    public function items(): HasMany
    {
        return $this->hasMany(OutboundItem::class, 'outbound_id', 'id');
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
