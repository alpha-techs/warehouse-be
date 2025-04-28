<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $maruokaJapan = [
            'id' => 1,
            'name' => '株式会社マルオカジャパン',
            'tel' => '03-6780-6575',
            'fax' => '03-6780-7326',
            'postal_code' => '143-0023',
            'detail_address1' => '東京都大田区山王１丁目５−３',
            'detail_address2' => 'メゾンド山王209',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        Customer::insertOrIgnore($maruokaJapan);
    }
}
