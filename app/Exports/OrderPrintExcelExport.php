<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderPrint;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class OrderPrintExcelExport
{
    public function __construct(
        private readonly Order $order,
        private readonly OrderPrint $print,
    ) {}

    public function toBinary(): string
    {
        // 模板文件路径
        $templatePath = resource_path('views/reports/order_print_template.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('注文書のテンプレートファイルが見つかりません: ' . $templatePath);
        }

        // 加载模板并填充数据
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        // 填充数据
        $this->fillTemplateData($sheet);

        // 保存文件
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        if ($binary === false) {
            throw new RuntimeException('Failed to capture order print Excel content.');
        }

        return $binary;
    }

    /**
     * 在模板工作表中填充数据
     */
    private function fillTemplateData(Worksheet $sheet): void
    {
        // お客様
        $sheet->setCellValue('A2', $this->order->customer_name . '御中');
        // 注文番号
        $sheet->setCellValue('I2', 'No: '. $this->order->order_number);
        // 発注日
        $sheet->setCellValue('I3', '発注日: ' . $this->order->created_at->format('Y/m/d'));
        // 納期
        $sheet->setCellValue('B11', $this->order->delivery_due_date?->format('Y/m/d'));
        // 納品場所
        $sheet->setCellValue('B12', '〒' . $this->order->delivery_postal_code . ' ' . $this->order->delivery_detail_address1 . ' ' . $this->order->delivery_detail_address2);
        // 担当
        $sheet->setCellValue('B13', '担当: ' . $this->order->contact_name);
        // 電話番号
        $sheet->setCellValue('B14', 'TEL: ' . $this->order->contact_phone);

        // 填充商品数据
        $dataStartRow = 16;
        $templateRow = 16;

        if (!empty($this->order->items)) {
            foreach ($this->order->items as $index => $item) {
                $currentRow = $dataStartRow + $index;

                // 如果不是第一行数据，需要复制模板行的格式
                if ($index > 0) {
                    $this->copyDataRow($sheet, $templateRow, $currentRow);
                }

                $sheet->setCellValue('A' . $currentRow, $item->product->name);
                $sheet->setCellValue('D' . $currentRow, $item->quantity);
                $sheet->setCellValue('E' . $currentRow, $item->unit);
                $sheet->setCellValue('F' . $currentRow, '¥' . ($item->unit_price ? number_format($item->unit_price) : ''));
                $sheet->setCellValue('G' . $currentRow, $item->note);
            }
        }
    }

    private function copyDataRow(Worksheet $sheet, int $srcRow, int $dstRow)
    {
        $sheet->insertNewRowBefore($dstRow);
        $targetColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',];

        // 复制行高
        $height = $sheet->getRowDimension($srcRow)->getRowHeight();
        $sheet->getRowDimension($dstRow)->setRowHeight($height);

        // 复制合并单元格
        $mergedCells = $sheet->getMergeCells();
        foreach ($mergedCells as $mergedCell) {
            if (preg_match('/(\d+)$/', $mergedCell, $m) && (int)$m[1] === $srcRow) {
                // 替换行号
                $newMerge = preg_replace('/\d+/', $dstRow, $mergedCell);
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
