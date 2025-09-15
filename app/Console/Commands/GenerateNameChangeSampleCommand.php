<?php

namespace App\Console\Commands;

use App\Models\NameChange;
use App\Models\NameChangeReport;
use App\Models\NameChangeItem;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Models\Product;
use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Exports\NameChangeExcelExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class GenerateNameChangeSampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'name-change:generate-sample {--output= : 指定输出文件名}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成名义变更Excel报表样例文件，用于测试和调整格式';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('开始生成名义变更Excel报表样例...');

        try {
            // 创建样例数据
            $sampleData = $this->createSampleData();

            // 生成文件名
            $fileName = $this->option('output') ?: 'name_change_sample_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filePath = 'reports/name_change/' . $fileName;

            // 确保目录存在
            Storage::disk('public')->makeDirectory('reports/name_change');

            // 检查是否有模板文件
            $templatePath = resource_path('views/reports/name_change_template.xlsx');

            if (!file_exists($templatePath)) {
                $this->error("⚠️  模板文件不存在，无法生成样例");
                return 1;
            }

            // 使用重构后的 NameChangeExcelExport
            $export = new NameChangeExcelExport(
                $sampleData['nameChange'],
                $sampleData['report'],
                $sampleData['items'],
                $sampleData['warehouse'],
                $sampleData['owner'],
                $sampleData['customer'],
            );

            $fullPath = storage_path('app/public/' . $filePath);
            $export->store($fullPath);
            $this->info("✅ 使用重构后的 NameChangeExcelExport 生成样例");

            $this->info("样例Excel文件已生成: {$fullPath}");
            $this->info("文件大小: " . number_format(filesize($fullPath) / 1024, 2) . " KB");
            $this->info("你可以打开这个文件来查看和调整格式。");

            // 显示样例数据信息
            $this->displaySampleDataInfo($sampleData);

        } catch (\Exception $e) {
            $this->error("生成样例文件时出错: " . $e->getMessage());
            $this->error("错误详情: " . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }


    /**
     * 创建样例数据
     */
    private function createSampleData(): array
    {
        $owner = new Customer();
        $owner->id = 1;
        $owner->name = '株式会社サンプル商事';
        $owner->tel = '03-1234-5678';
        $owner->fax = '03-1234-5679';
        $owner->postal_code = '100-0001';
        $owner->detail_address1 = '東京都千代田区千代田';
        $owner->detail_address2 = '';

        // 创建样例客户
        $customer = new Customer();
        $customer->id = 1;
        $customer->name = '株式会社サンプル商事';
        $customer->tel = '03-1234-5678';
        $customer->fax = '03-1234-5679';
        $customer->postal_code = '100-0001';
        $customer->detail_address1 = '東京都千代田区千代田';
        $customer->detail_address2 = '1-1-1 サンプルビル 5F';

        // 创建客户联系人
        $contact = new CustomerContact();
        $contact->id = 1;
        $contact->customer_id = 1;
        $contact->name = '田中太郎';
        $contact->tel = '03-1234-5678';
        $contact->email = 'tanaka@sample.co.jp';
        $customer->setRelation('contact', $contact);

        // 创建仓库
        $warehouse = new Warehouse();
        $warehouse->id = 1;
        $warehouse->name = '東扇島倉庫';
        $warehouse->address = '神奈川県川崎市川崎区東扇島';

        // 创建名义变更
        $nameChange = new NameChange();
        $nameChange->id = 1;
        $nameChange->name_change_order_id = 'NC-2024-001';
        $nameChange->name_change_date = '2024-12-15';
        $nameChange->warehouse_id = 1;
        $nameChange->warehouse_name = '東扇島倉庫';
        $nameChange->customer_id = 1;
        $nameChange->customer_name = '株式会社サンプル商事';
        $nameChange->status = 'pending';

        // 创建报告
        $report = new NameChangeReport();
        $report->id = 1;
        $report->name_change_id = 'MBYF-0101';
        $report->format = 'excel';
        $report->status = 'completed';
        $report->file_path = 'reports/name_change/sample.xlsx';

        // 创建商品
        $products = [
            [
                'id' => 1,
                'name' => 'サンプル商品A',
                'specification' => '500g×24個入り',
            ],
            [
                'id' => 2,
                'name' => 'サンプル商品B',
                'specification' => '1kg×12個入り',
            ],
            [
                'id' => 3,
                'name' => 'サンプル商品C',
                'specification' => '250g×48個入り',
            ],
        ];

        // 创建库存项目
        $inventoryItems = [
            [
                'id' => 1,
                'unit_weight' => 0.5,
                'expiry_date' => '2025-06-30',
            ],
            [
                'id' => 2,
                'unit_weight' => 1.0,
                'expiry_date' => '2025-08-15',
            ],
            [
                'id' => 3,
                'unit_weight' => 0.25,
                'expiry_date' => '2025-05-20',
            ],
        ];

        // 创建名义变更项目
        $items = collect();

        foreach ($products as $index => $productData) {
            $product = new Product();
            $product->id = $productData['id'];
            $product->name = $productData['name'];
            $product->dimension_description = $productData['specification']; // 使用正确的字段名

            $inventoryItem = new InventoryItem();
            $inventoryItem->id = $inventoryItems[$index]['id'];
            $inventoryItem->per_item_weight = $inventoryItems[$index]['unit_weight']; // 使用正确的字段名
            $inventoryItem->best_before_date = $inventoryItems[$index]['expiry_date']; // 使用正确的字段名

            $item = new NameChangeItem();
            $item->id = $index + 1;
            $item->name_change_id = 1;
            $item->product_id = $productData['id'];
            $item->product_name = $productData['name'];
            $item->quantity = ($index + 1) * 10; // 10, 20, 30
            $item->lot_number = 'LOT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
            $item->note = 'サンプル備考' . ($index + 1);

            $item->setRelation('product', $product);
            $item->setRelation('inventoryItem', $inventoryItem);

            $items->push($item);
        }

        return [
            'nameChange' => $nameChange,
            'report' => $report,
            'items' => $items,
            'warehouse' => $warehouse,
            'owner' => $owner,
            'customer' => $customer,
        ];
    }

    /**
     * 显示样例数据信息
     */
    private function displaySampleDataInfo(array $sampleData): void
    {
        $this->newLine();
        $this->info('样例数据信息:');
        $this->line('客户: ' . $sampleData['customer']->name);
        $this->line('仓库: ' . $sampleData['warehouse']->name);
        $this->line('名义变更日期: ' . $sampleData['nameChange']->name_change_date);
        $this->line('商品数量: ' . $sampleData['items']->count());

        $this->newLine();
        $this->info('商品明细:');
        foreach ($sampleData['items'] as $item) {
            $this->line("- {$item->product->name}: {$item->quantity}甲");
        }
    }
}
