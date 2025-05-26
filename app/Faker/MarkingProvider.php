<?php

namespace App\Faker;

use Faker\Provider\Base;

class MarkingProvider extends Base
{
    protected array $boxTypes = [
        '白箱', '茶箱', 'ダンボール', '黒箱'
    ];

    protected array $sealingTypes = [
        '白バンド', '黄バンド', '透明バンド',
        '白テープ', '黄テープ', '透明テープ', 'PPバンド'
    ];

    public function marking(): string
    {
        $box = $this->generator->randomElement($this->boxTypes);
        $seal = $this->generator->randomElement($this->sealingTypes);

        return "$box $seal";
    }
}
