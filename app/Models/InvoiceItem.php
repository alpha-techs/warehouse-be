<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\InvoiceItem
 *
 * @property int $id
 * @property int $invoice_id 发票ID
 * @property int|null $outbound_id 出库单ID
 * @property int|null $outbound_item_id 出库明细ID
 * @property string|null $outbound_order_id 出库订单编号
 * @property string|null $outbound_date 出库日期
 * @property int|null $product_id 商品ID
 * @property string|null $product_name 商品名称
 * @property int $quantity 数量
 * @property string $currency 币种
 * @property int $unit_price 单价
 * @property int $line_amount 行金额
 * @property int $tax_amount 税额
 * @property string|null $note 备注
 * @property-read \App\Models\Invoice $invoice
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\Outbound|null $outbound
 * @property-read \App\Models\OutboundItem|null $outboundItem
 */
class InvoiceItem extends BaseModel
{
    protected $casts = [
        'outbound_date' => 'date',
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'line_amount' => 'integer',
        'tax_amount' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class, 'outbound_id', 'id');
    }

    public function outboundItem(): BelongsTo
    {
        return $this->belongsTo(OutboundItem::class, 'outbound_item_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
