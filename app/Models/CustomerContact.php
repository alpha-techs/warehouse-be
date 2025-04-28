<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\CustomerContact
 *
 * @property int $id
 * @property int $customer_id 客户ID
 * @property string $name 姓名
 * @property string|null $tel 电话
 * @property string|null $email 邮箱地址
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Customer|null $customer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerContact withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class CustomerContact extends BaseModel
{
    use SoftDeletes;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
