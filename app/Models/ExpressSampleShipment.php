<?php

namespace App\Models;

use App\Contracts\Models\ExpressSampleShipmentStatus;
use App\Observers\ExpressSampleShipmentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\ExpressSampleShipment
 *
 * @property int $id
 * @property string|null $express_sample_order_id 急送样品订单编号
 * @property ExpressSampleShipmentStatus $status 急送样品出库状态
 * @property \Illuminate\Support\Carbon|null $requested_ship_date 希望出库日期
 * @property \Illuminate\Support\Carbon|null $desired_delivery_date 希望送达日期
 * @property string|null $desired_delivery_time_window 希望送达时间段
 * @property int $warehouse_id 仓库ID
 * @property string|null $warehouse_name 仓库名称
 * @property int|null $customer_id 客户ID
 * @property string|null $customer_name 客户名称
 * @property int|null $customer_contact_id 客户联系人ID
 * @property string $delivery_service 宅急便类型
 * @property int $package_count 包裹箱数
 * @property string|null $package_type 包装类型说明
 * @property string|null $delivery_fee_payer 运费承担方
 * @property string|null $sample_purpose 样品发送目的
 * @property string|null $recipient_company_name 收件公司名称
 * @property string|null $recipient_department 收件部门
 * @property string|null $recipient_name 收件人姓名
 * @property string|null $recipient_phone_number 收件人电话
 * @property string|null $recipient_postal_code 邮政编码
 * @property string|null $recipient_prefecture 都道府县
 * @property string|null $recipient_city 市区町村
 * @property string|null $recipient_address_line1 地址行1
 * @property string|null $recipient_address_line2 地址行2
 * @property string|null $emergency_contact_name 紧急联系人姓名
 * @property string|null $emergency_contact_phone_number 紧急联系电话
 * @property string|null $carrier_name 承运商名称
 * @property string|null $tracking_number 追踪编号
 * @property string|null $note 备注
 * @property \Illuminate\Support\Carbon|null $dispatched_at 实际发货时间
 * @property \Illuminate\Support\Carbon|null $delivered_at 实际送达时间
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\CustomerContact|null $customerContact
 * @property-read string $desired_delivery_time_window_description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpressSampleShipmentItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ExpressSampleShipmentReport> $reports
 * @property-read int|null $reports_count
 * @property-read \App\Models\Warehouse|null $warehouse
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereCarrierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereCustomerContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDeliveredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDeliveryFeePayer($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDeliveryService($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDesiredDeliveryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDesiredDeliveryTimeWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereDispatchedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereEmergencyContactPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereExpressSampleOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment wherePackageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment wherePackageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientAddressLine1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientAddressLine2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientDepartment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientPostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRecipientPrefecture($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereRequestedShipDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereSamplePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment whereWarehouseName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpressSampleShipment withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
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

    public function getDesiredDeliveryTimeWindowDescriptionAttribute(): string
    {
        return match ($this->desired_delivery_time_window) {
            'morning' => '午前中',
            'noon' => '正午',
            'afternoon' => '午後',
            'evening' => '夕方',
            'anytime' => '指定なし',
            default => '指定なし',
        };
    }
}
