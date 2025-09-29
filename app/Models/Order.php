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
 * @property string $order_number
 * @property OrderStatus $status
 * @property int $customer_id
 * @property string|null $customer_name
 * @property \Illuminate\Support\Carbon $delivery_due_date
 * @property string|null $delivery_postal_code
 * @property string|null $delivery_detail_address1
 * @property string|null $delivery_detail_address2
 * @property string $contact_name
 * @property string $contact_phone
 * @property string $currency
 * @property float $total_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $cancelled_at
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
