<?php

namespace App\Models;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Observers\ExpressSampleShipmentItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $express_sample_shipment_id
 * @property ExpressSampleShipmentStatus|null $shipment_status
 * @property int $inventory_item_id
 * @property int|null $inbound_item_id
 * @property int $product_id
 * @property string|null $product_name
 * @property int $quantity
 * @property string|null $quantity_unit
 * @property string|null $sample_packaging
 * @property string|null $lot_number
 * @property string|null $inbound_no
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $inbound_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExpressSampleShipment $expressSampleShipment
 * @property-read \App\Models\InventoryItem $inventoryItem
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipmentItem query()
 */
#[ObservedBy(ExpressSampleShipmentItemObserver::class)]
class ExpressSampleShipmentItem extends BaseModel
{
    use SoftDeletes;

    protected $hidden = [
        'deleted_at',
    ];

    protected $casts = [
        'shipment_status' => ExpressSampleShipmentStatus::class,
        'inbound_date' => 'date',
        'quantity' => 'integer',
    ];

    public function expressSampleShipment(): BelongsTo
    {
        return $this->belongsTo(ExpressSampleShipment::class, 'express_sample_shipment_id', 'id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function inboundItem(): BelongsTo
    {
        return $this->belongsTo(InboundItem::class, 'inbound_item_id', 'id');
    }
}
