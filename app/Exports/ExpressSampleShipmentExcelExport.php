<?php

namespace App\Exports;

use App\Models\ExpressSampleShipment;
use App\Models\ExpressSampleShipmentItem;
use App\Models\ExpressSampleShipmentReport;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;

class ExpressSampleShipmentExcelExport
{
    public function __construct(
        protected ExpressSampleShipment $shipment,
        protected ExpressSampleShipmentReport $report,
    ) {
    }

    public function toBinary(): string
    {
        // 模板文件路径
        $templatePath = resource_path('views/reports/express_sample_template.xlsx');

        if (!file_exists($templatePath)) {
            throw new \Exception('宅急便発送依頼書のテンプレートファイルが見つかりません: ' . $templatePath);
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
            throw new RuntimeException('Failed to render express sample shipment Excel content.');
        }

        return $binary;
    }

    /**
     * 在模板工作表中填充数据
     */
    private function fillTemplateData(Worksheet $sheet): void
    {
        // 倉庫 FAX
        $sheet->setCellValue('A3', 'FAX:' . ($this->shipment->warehouse->fax ?? ''));
        // 倉庫名
        $sheet->setCellValue('A4', $this->shipment->warehouse->name . '御中');

        // 填充商品数据
        $templateRow = 11;
        $currentRow = 11;

        if (!empty($this->shipment->items)) {
            foreach ($this->shipment->items as $index => $item) {
                $currentRow += ($index * 4);

                // 如果不是前两行数据，需要复制模板行的格式
                if ($index > 1) {
                    $this->copyDataRows(
                        $sheet,
                        [$templateRow, $templateRow + 1, $templateRow + 2, $templateRow + 3],
                        $currentRow
                    );
                }

                $sheet->setCellValue('B' . $currentRow, $item->product->name ?? '');
                $sheet->setCellValue('B' . ($currentRow + 1), $item->inbound_no ?? '');
                $sheet->setCellValue('B' . ($currentRow + 2), $item->inbound_date ?? '');
                $sheet->setCellValue('B' . ($currentRow + 3), ($item->quantity ?? 0) . ($item->quantity_unit ?? ''));
            }
        }

        $currentRow += 4;
        $currentRow = max($currentRow, 19);
        // 配送先
        $sheet->setCellValue('B' . $currentRow, $this->shipment->recipient_company_name);
        $sheet->setCellValue('B' . ($currentRow + 1), $this->shipment->recipient_name ? $this->shipment->recipient_name . ' 様' : '');
        $sheet->setCellValue('B' . ($currentRow + 2), '〒' . $this->shipment->recipient_postal_code . ' ' . ($this->shipment->recipient_address_line1 ?? '') . ' ' . $this->shipment->recipient_address_line2 ?? '');
        $sheet->setCellValue('B' . ($currentRow + 3), 'TEL: ' . $this->shipment->recipient_phone_number);

        // 到着日
        $desired_delivery_date = $this->shipment->desired_delivery_date ? Carbon::parse($this->shipment->desired_delivery_date)->format('Y-m-d') : '';
        $desired_delivery_time_window = $this->shipment->desired_delivery_time_window_description;
        $sheet->setCellValue(
            'B' . ($currentRow + 4),
            $desired_delivery_date . ' ' . $desired_delivery_time_window
        );

        // 備考
        $sheet->setCellValue('B' . ($currentRow + 5), $this->shipment->note ?? '');

    }

    private function copyDataRows(Worksheet $sheet, array $srcRows, int $dstRow)
    {
        foreach ($srcRows as $index => $row) {
            $this->copyDataRow($sheet, $row, $dstRow + $index);
        }
    }

    private function copyDataRow(Worksheet $sheet, int $srcRow, int $dstRow)
    {
        $sheet->insertNewRowBefore($dstRow);
        $targetColumns = ['A', 'B', 'C', 'D', 'E', 'F'];

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

