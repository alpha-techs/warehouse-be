<?php

namespace App\Models;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Observers\ExpressSampleShipmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string|null $express_sample_order_id
 * @property ExpressSampleShipmentStatus $status
 * @property string|null $requested_ship_date
 * @property string|null $desired_delivery_date
 * @property string|null $desired_delivery_time_window
 * @property int $warehouse_id
 * @property string|null $warehouse_name
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property int|null $customer_contact_id
 * @property string|null $delivery_service
 * @property int $package_count
 * @property string|null $package_type
 * @property string|null $delivery_fee_payer
 * @property string|null $sample_purpose
 * @property string|null $recipient_company_name
 * @property string|null $recipient_department
 * @property string|null $recipient_name
 * @property string|null $recipient_phone_number
 * @property string|null $recipient_postal_code
 * @property string|null $recipient_prefecture
 * @property string|null $recipient_city
 * @property string|null $recipient_address_line1
 * @property string|null $recipient_address_line2
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone_number
 * @property string|null $carrier_name
 * @property string|null $tracking_number
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $dispatched_at
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpressSampleShipmentItem> $items
 * @property-read \App\Models\Warehouse|null $warehouse
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\CustomerContact|null $customerContact
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment query()
 */
#[ObservedBy(ExpressSampleShipmentObserver::class)]
class ExpressSampleShipment extends BaseModel
{
    use SoftDeletes;

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'status' => ExpressSampleShipmentStatus::class,
        'requested_ship_date' => 'date',
        'desired_delivery_date' => 'date',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'package_count' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ExpressSampleShipmentItem::class, 'express_sample_shipment_id', 'id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function customerContact(): BelongsTo
    {
        return $this->belongsTo(CustomerContact::class, 'customer_contact_id', 'id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ExpressSampleShipmentReport::class, 'express_sample_shipment_id', 'id');
    }
}
