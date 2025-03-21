<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 * @property int $id
 * @property string $name 商品名称
 * @property string|null $sku 商品编码
 * @property int|null $leaf_category_id 最小商品种类ID
 * @property string|null $cargo_mark 货品标记
 * @property string|null $dimension_description 尺寸描述
 * @property string|null $length 长度
 * @property string|null $width 宽度
 * @property string|null $height 高度
 * @property string|null $weight 重量
 * @property string|null $length_unit 长度单位
 * @property string|null $weight_unit 重量单位
 * @property bool $has_sub_package 是否存在子包装
 * @property string|null $sub_package_description 子包装描述
 * @property int|null $sub_package_count 子包装数量
 * @property bool|null $is_fixed_weight 是否固定重量
 * @property int $isActive 是否启用
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read array $dimension
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCargoMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDimensionDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHasSubPackage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsFixedWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLeafCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLengthUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSubPackageCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSubPackageDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWeightUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 * @mixin \Eloquent
 * @noinspection PhpFullyQualifiedNameUsageInspection
 * @noinspection PhpUnnecessaryFullyQualifiedNameInspection
 */
class Product extends BaseModel
{
    use softDeletes;

    protected $casts = [
        'has_sub_package' => 'boolean',
        'is_fixed_weight' => 'boolean'
    ];

    protected $appends = ['dimension'];

    public function dimension(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => [
                'description' => $this->dimension_description,
                'length' => $this->length,
                'width' => $this->width,
                'height' => $this->height,
                'weight' => $this->weight,
                'length_unit' => $this->length_unit,
                'weight_unit' => $this->weight_unit,
            ],
        );
    }
}
