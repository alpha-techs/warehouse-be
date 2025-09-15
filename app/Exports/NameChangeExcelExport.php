<?php

namespace App\Exports;

use App\Models\Customer;
use App\Models\NameChange;
use App\Models\NameChangeItem;
use App\Models\NameChangeReport;
use App\Models\Warehouse;
use ArrayAccess;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NameChangeExcelExport
{
    public function __construct(
        protected NameChange $nameChange,
        protected NameChangeReport $report,
        /** @var NameChangeItem[] $items */
        protected ArrayAccess $items,
        protected Warehouse $warehouse,
        protected Customer $owner,
        protected Customer $customer)
    {
    }

    /**
     * 生成Excel文件到指定路径
     */
    public function store(string $filePath): string
    {
        // 模板文件路径
        $templatePath = resource_path('views/reports/name_change_template.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('名義変更依頼書のテンプレートファイルが見つかりません: ' . $templatePath);
        }

        // 确保目标目录存在
        $destinationDir = dirname($filePath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // 复制模板文件到目标位置
        if (!copy($templatePath, $filePath)) {
            throw new \Exception('テンプレートファイルのコピーに失敗しました。');
        }

        // 加载复制的文件并填充数据
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 填充数据
        $this->fillTemplateData($sheet);

        // 保存文件
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * 在模板工作表中填充数据
     */
    private function fillTemplateData(Worksheet $sheet): void
    {
        $sheet->setCellValue('N2', 'NO: ' . ($this->report->name_change_id ?? ''));
        $sheet->setCellValue('A3', $this->warehouse->name);
        $sheet->setCellValue('I3', 'FAX: ' . ($this->warehouse->fax ?? ''));
        $sheet->setCellValue('N3', '作成日: ' . now()->format('Y年m月d日'));
        $sheet->setCellValue('B5', $this->nameChange->name_change_date);
        $sheet->setCellValue('N4', $this->customer->name);
        $address = $this->customer->detail_address1 . $this->customer->detail_address2;
        $sheet->setCellValue('N5', $address);
        $contactString = 'TEL: ' . $this->customer->tel . ' FAX: ' . $this->customer->fax;
        $sheet->setCellValue('N6', $contactString);

        // 填充商品数据
        $dataStartRow = 8;
        $templateRow = 8;
        $totalQuantity = 0;
        $totalWeight = 0;
        $weightUnit = '';

        if (!empty($this->items)) {
            foreach ($this->items as $index => $item) {
                $currentRow = $dataStartRow + $index;

                // 如果不是第一行数据，需要复制模板行的格式
                if ($index > 0) {
                    $this->copyDataRow($sheet, $templateRow, $currentRow);
                }

                $unitWeight = $item->inventoryItem->per_item_weight;
                $weight = $unitWeight * $item->quantity;
                $totalQuantity += $item->quantity;
                $totalWeight += $weight;
                $weightUnit = $item->inventoryItem->per_item_weight_unit;

                $expiryDate = '';
                if ($item->inventoryItem?->best_before_date) {
                    $expiryDate = Carbon::parse($item->inventoryItem->best_before_date)->format('Y/m/d');
                }

                $sheet->setCellValue('A' . $currentRow, $item->product->name ?? '');
                $sheet->setCellValue('C' . $currentRow, $item->product->dimension_description ?? '');
                $sheet->setCellValue('G' . $currentRow, $unitWeight . $weightUnit);
                $sheet->setCellValue('H' . $currentRow, $item->quantity);
                $sheet->setCellValue('I' . $currentRow, number_format($weight, 0) . $weightUnit);
                $sheet->setCellValue('K' . $currentRow, $item->lot_number ?? '');
                $sheet->setCellValue('N' . $currentRow, $expiryDate);
                $sheet->setCellValue('Q' . $currentRow, $item->note ?? '');
            }
        }

        // 填充合计行
        $totalRow = $dataStartRow + max(count($this->items), 1);
        $sheet->setCellValue('H' . $totalRow, $totalQuantity);
        $sheet->setCellValue('I' . $totalRow, number_format($totalWeight, 0) . $weightUnit);

        // 填充名义变更信息
        $infoStartRow = $totalRow + 1;
        $addressString = $this->customer->detail_address1 . $this->customer->detail_address2;
        $sheet->setCellValue('C' . $infoStartRow, $this->customer->name);
        $sheet->setCellValue('C' . ($infoStartRow + 1), $addressString);
        $sheet->setCellValue('C' . ($infoStartRow + 2), $this->customer->contact?->name ?? '');
        $sheet->setCellValue('C' . ($infoStartRow + 3), $this->customer->tel);
        $sheet->setCellValue('C' . ($infoStartRow + 4), $this->customer->fax);

        $lastDate = Carbon::parse($this->nameChange->name_change_date);
        $lastDateString = $lastDate->format('Y/m/d');
        $nextDateString = $lastDate->addDay()->format('Y/m/d');
        $sheet->setCellValue('K' . ($infoStartRow + 1), $lastDateString . 'まで: '. $this->owner->name .' 負担');
        $sheet->setCellValue('K' . ($infoStartRow + 2), $nextDateString . 'から: '. $this->customer->name .' 負担');

        $sheet->setCellValue('A' . ($infoStartRow + 5), '返信FAX('. $this->owner->fax .') お願いします。');
    }

    private function copyDataRow(Worksheet $sheet, int $srcRow, int $dstRow)
    {
        $sheet->insertNewRowBefore($dstRow);
        $targetColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];

        // 复制行高
        $height = $sheet->getRowDimension($srcRow)->getRowHeight();
        $sheet->getRowDimension($dstRow)->setRowHeight($height);

        // 复制合并单元格
        $mergedCells = $sheet->getMergeCells();
        foreach ($mergedCells as $mergedCell) {
            if (preg_match('/(\d+)$/', $mergedCell, $m) && (int)$m[1] === $srcRow) {
                print_r("Get merged cell: " . $mergedCell . PHP_EOL);
                // 替换行号
                $newMerge = preg_replace('/\d+/', $dstRow, $mergedCell);
                print_r("Merge cell: " . $newMerge . PHP_EOL);
                $sheet->mergeCells($newMerge);
            }
        }

        // 遍历列
        foreach ($targetColumns as $col) {
            $srcCell = $sheet->getCell($col . $srcRow);
            $dstCell = $sheet->getCell($col . $dstRow);

            // 设置为空
            $dstCell->setValue(null);

            // 复制样式
            $style = $sheet->getStyle($col . $srcRow);
            $sheet->duplicateStyle($style, $dstCell->getCoordinate());
        }
    }
}
