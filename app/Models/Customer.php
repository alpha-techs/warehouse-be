<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Customer
 *
 * @property int $id
 * @property string $name 名称
 * @property string|null $tel 电话
 * @property string|null $fax 邮箱
 * @property string|null $postal_code 邮政编码
 * @property string|null $detail_address1 详细地址1
 * @property string|null $detail_address2 详细地址2
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CustomerContact|null $contact
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDetailAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDetailAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Customer extends BaseModel
{
    use softDeletes;

    protected $appends = ['address'];

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (Customer $customer) {
            $customer->contact()->delete();
        });
    }

    public function contact(): HasOne
    {
        return $this->hasOne(CustomerContact::class, 'customer_id', 'id');
    }

    public function address(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => [
                'postal_code' => $this->postal_code,
                'detail_address1' => $this->detail_address1,
                'detail_address2' => $this->detail_address2,
            ],
            set: fn (mixed $value, array $attributes) => [
                'postal_code' => $value['postalCode'] ?? null,
                'detail_address1' => $value['detailAddress1'] ?? null,
                'detail_address2' => $value['detailAddress2']  ?? null,
            ],
        );
    }
}
