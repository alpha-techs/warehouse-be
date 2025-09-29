<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property string|null $product_name
 * @property string|null $product_sku
 * @property float $quantity
 * @property string $unit
 * @property float $unit_price
 * @property float $line_amount
 * @property string $currency
 * @property string|null $note
 */
class OrderItem extends BaseModel
{
    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'float',
        'line_amount' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
