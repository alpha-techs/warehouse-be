<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property string $name 名称
 * @property string|null $tel 电话
 * @property string|null $fax 传真
 * @property string|null $postal_code 邮政编码
 * @property string|null $detail_address1 详细地址1
 * @property string|null $detail_address2 详细地址2
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereDetailAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereDetailAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereFax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereTel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Warehouse extends BaseModel
{
    use softDeletes;
}
