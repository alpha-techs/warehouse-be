<?php

namespace App\Faker;

use Faker\Provider\Base;

class PackagingSpecProvider extends Base
{
    protected array $unitWeights = [40, 50, 60, 80, 100, 120]; // g
    protected array $countsPerInner = [20, 30, 50, 100, 120, 150];
    protected array $outerCounts = [1, 2, 3, 5]; // X2合

    public function packagingSpec(): string
    {
        $unitWeight = $this->generator->randomElement($this->unitWeights); // 单片重量
        $innerCount = $this->generator->randomElement($this->countsPerInner); // 单位数量（枚）
        $outerCount = $this->generator->randomElement($this->outerCounts); // 合数（外包装数量）

        // 总重（估算为 unitWeight * innerCount * outerCount / 1000）保留1位小数
        $totalWeight = round(($unitWeight * $innerCount * $outerCount) / 1000, 1); // kg
        $showTotalWeight = $this->generator->boolean(60); // 60% 机率显示总重量

        $base = "{$unitWeight}gX{$innerCount}枚";

        if ($showTotalWeight) {
            $total = "{$totalWeight}kg({$base})";
        } else {
            $total = $base;
        }

        if ($outerCount > 1) {
            $total .= "X{$outerCount}合";
        }

        return $total;
    }
}
