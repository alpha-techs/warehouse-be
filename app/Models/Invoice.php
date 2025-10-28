<?php

namespace App\Models;

use App\Contracts\Models\InvoiceStatus;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Invoice
 *
 * @property int $id
 * @property string $invoice_number 发票编号
 * @property InvoiceStatus $status 发票状态
 * @property int $outbound_id 出库单ID
 * @property int $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property string|null $due_date 付款截止日期
 * @property string|null $issue_date 下发时间
 * @property string $currency 币种
 * @property int $subtotal_amount 商品小计金额
 * @property int $tax_amount 税额
 * @property int $total_amount 总金额
 * @property string|null $issue_message 下发消息
 * @property string|null $cancel_reason 取消原因
 * @property string|null $notes 备注
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\Outbound|null $outbound
 */
#[ObservedBy(InvoiceObserver::class)]
class Invoice extends BaseModel
{
    use SoftDeletes;

    protected $casts = [
        'status' => InvoiceStatus::class,
        'due_date' => 'date',
        'issue_date' => 'datetime',
        'subtotal_amount' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id', 'id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function outbound(): BelongsTo
    {
        return $this->belongsTo(Outbound::class, 'outbound_id', 'id');
    }
}
