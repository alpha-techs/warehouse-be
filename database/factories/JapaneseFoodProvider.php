<?php

namespace Database\Factories;

use Faker\Provider\Base;

class JapaneseFoodProvider extends Base
{
    protected static $freshFoods = [
        '新鮮な鮭',
        '生サーモン',
        '新鮮なマグロ',
        '生ホタテ',
        '新鮮なイカ',
        '生エビ',
        '新鮮なカニ',
        '生牡蠣',
        '新鮮なアワビ',
        '生ウニ',
        '新鮮なブリ',
        '生ヒラメ',
        '新鮮なカンパチ',
        '生アジ',
        '新鮮なサバ',
    ];

    protected static $frozenFoods = [
        '冷凍マグロ',
        '冷凍サーモン',
        '冷凍エビ',
        '冷凍ホタテ',
        '冷凍イカ',
        '冷凍カニ',
        '冷凍ブリ',
        '冷凍サバ',
        '冷凍アジ',
        '冷凍カンパチ',
        '冷凍ヒラメ',
        '冷凍牡蠣',
        '冷凍アワビ',
        '冷凍ウニ',
        '冷凍イクラ',
    ];

    public function japaneseFreshFood()
    {
        return static::randomElement(static::$freshFoods);
    }

    public function japaneseFrozenFood()
    {
        return static::randomElement(static::$frozenFoods);
    }

    public function japaneseFood()
    {
        return mt_rand(1, 100) <= 70 
            ? $this->japaneseFreshFood() 
            : $this->japaneseFrozenFood();
    }
} 