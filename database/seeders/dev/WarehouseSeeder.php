<?php

namespace Database\Seeders\dev;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'id' => 1,
                'name' => 'ベニレイロジスティクス　東扇島事業所',
                'tel' => '044-266-9000',
                'fax' => '044-266-9300',
                'postal_code' => '210-0869',
                'detail_address1' => '神奈川県川崎市川崎区東扇島17-2',
                'detail_address2' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '西濃運輸 厚木支店',
                'tel' => '046-285-0881',
                'fax' => '0462852315',
                'postal_code' => '243-0303',
                'detail_address1' => '神奈川県愛甲郡愛川町中津４０３０',
                'detail_address2' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '西濃運輸 セイノースーパーエクスプレス株式会社平和島営業所(航空)',
                'tel' => '03-5493-2441',
                'fax' => '03-5493-2426',
                'postal_code' => '143-0006',
                'detail_address1' => '大田区平和島2-1-1（京浜ターミナル内）',
                'detail_address2' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        Warehouse::insertOrIgnore($warehouses);
    }
}
