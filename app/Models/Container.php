<?php

namespace App\Models;

use App\Contracts\Models\ContainerStatus;
use App\Observers\ContainerObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Container
 *
 * @property int $id
 * @property string $container_number 集装箱号
 * @property string|null $shipping_line 船公司
 * @property string|null $vessel_name 船名
 * @property string|null $voyage_number 航次号
 * @property \Illuminate\Support\Carbon|null $arrival_date 到港日期
 * @property \Illuminate\Support\Carbon|null $clearance_date 清关日期
 * @property \Illuminate\Support\Carbon|null $discharge_date 卸货日期
 * @property \Illuminate\Support\Carbon|null $return_date 空箱归还日期
 * @property ContainerStatus $status 状态
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ContainerItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereArrivalDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereClearanceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereContainerNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereDischargeDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereReturnDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereShippingLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereVesselName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereVoyageNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
#[ObservedBy(ContainerObserver::class)]
class Container extends BaseModel
{
    use SoftDeletes, HasFactory;

    protected $casts = [
        'arrival_date' => 'date',
        'clearance_date' => 'date',
        'discharge_date' => 'date',
        'return_date' => 'date',
        'status' => ContainerStatus::class,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ContainerItem::class, 'container_id', 'id');
    }
}
