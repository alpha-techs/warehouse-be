<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\InvoicePrint;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class InvoicePrintExcelExport
{
    public function __construct(
        private readonly Invoice $invoice,
        private readonly InvoicePrint $print,
    ) {}

    public function toBinary(): string
    {
        // 模板文件路径
        $templatePath = resource_path('views/reports/inv_print_template.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('請求書のテンプレートファイルが見つかりません: ' . $templatePath);
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
            throw new RuntimeException('Failed to capture invoice print Excel content.');
        }

        return $binary;
    }

    /**
     * 在模板工作表中填充数据
     */
    private function fillTemplateData(Worksheet $sheet): void
    {
        // お客様住所
        $sheet->setCellValue('A3', '〒' . $this->invoice->customer->postal_code);
        $sheet->setCellValue('A4', $this->invoice->customer->detail_address1 . ' ' . $this->invoice->customer->detail_address2);
        // お客様
        $sheet->setCellValue('A5', $this->invoice->customer_name);
        // 請求番号
        $sheet->setCellValue('AA3', $this->invoice->invoice_number);
        // 請求発行日
        $sheet->setCellValue('AA4', $this->invoice->created_at->format('Y/m/d'));
        // 登録番号
        $sheet->setCellValue('AA5', null);
        // ご請求額
        $sheet->setCellValue('E18', $this->invoice->total_amount);
        // お支払い期限
        $sheet->setCellValue('AD18', $this->invoice->due_date->format('Y/m/d'));

        // 填充商品数据
        $dataStartRow = 21;
        $templateRow = 21;

        if (!empty($this->invoice->items)) {
            foreach ($this->invoice->items as $index => $item) {
                $currentRow = $dataStartRow + $index;

                // 如果不是第一行数据，需要复制模板行的格式
                if ($index > 0) {
                    $this->copyDataRow($sheet, $templateRow, $currentRow);
                }

                $sheet->setCellValue('A' . $currentRow, $item->product->name);
                $sheet->setCellValue('L' . $currentRow, $item->quantity);
                $sheet->setCellValue('S' . $currentRow, null);
                $sheet->setCellValue('U' . $currentRow, ($item->unit_price ? number_format($item->unit_price) : ''));
                $sheet->setCellValue('Y' . $currentRow, $item->line_amount);
                $sheet->setCellValue('AD' . $currentRow, '8%');
                $sheet->setCellValue('AG' . $currentRow, $item->note);
            }
        }

        // 填充合计行
        $totalRow = $dataStartRow + count($this->invoice->items) + 1;
        $sheet->setCellValue('Y' . $totalRow, $this->invoice->subtotal_amount);
        $sheet->setCellValue('AD' . $totalRow, $this->invoice->tax_amount);
        $sheet->setCellValue('AI' . $totalRow, $this->invoice->total_amount);
    }

    private function copyDataRow(Worksheet $sheet, int $srcRow, int $dstRow)
    {
        $sheet->insertNewRowBefore($dstRow);
        $targetColumns = [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
            'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
            'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
            'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI',
            'AL', 'AM',
        ];

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
