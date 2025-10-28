<?php

namespace App\Models;

use App\Contracts\Models\OrderStatus;
use App\Observers\OrderObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $order_number 注文编号
 * @property OrderStatus $status 注文状态
 * @property int $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property \Illuminate\Support\Carbon $delivery_due_date 纳期
 * @property string|null $delivery_postal_code 纳品邮编
 * @property string|null $delivery_detail_address1 纳品地址1
 * @property string|null $delivery_detail_address2 纳品地址2
 * @property string $contact_name 负责人姓名
 * @property string $contact_phone 联系电话
 * @property string $currency 币种
 * @property float $total_amount 合计金额（未含税）
 * @property string|null $notes 备注
 * @property \Illuminate\Support\Carbon|null $cancelled_at 取消时间
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property mixed|null $delivery_address
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCancelledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryDetailAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryDetailAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereDeliveryPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereOrderNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
#[ObservedBy(OrderObserver::class)]
class Order extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        'status' => OrderStatus::class,
        'delivery_due_date' => 'date',
        'total_amount' => 'float',
        'cancelled_at' => 'datetime',
    ];

    protected $appends = ['delivery_address'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function deliveryAddress(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => [
                'postal_code' => $attributes['delivery_postal_code'] ?? null,
                'detail_address1' => $attributes['delivery_detail_address1'] ?? null,
                'detail_address2' => $attributes['delivery_detail_address2'] ?? null,
            ],
            set: function (mixed $value, array $attributes) {
                $address = is_array($value) ? $value : [];

                return [
                    'delivery_postal_code' => $address['postalCode'] ?? null,
                    'delivery_detail_address1' => $address['detailAddress1'] ?? null,
                    'delivery_detail_address2' => $address['detailAddress2'] ?? null,
                ];
            },
        );
    }
}
