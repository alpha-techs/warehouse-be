<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPrint;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Order');

        $sheet->setCellValue('A1', 'Order Print');
        $sheet->setCellValue('A2', 'Order Number');
        $sheet->setCellValue('B2', $this->order->order_number);
        $sheet->setCellValue('A3', 'Customer');
        $sheet->setCellValue('B3', $this->order->customer_name ?? $this->order->customer?->name);
        $sheet->setCellValue('A4', 'Status');
        $sheet->setCellValue('B4', $this->order->status?->value ?? $this->order->status);
        $sheet->setCellValue('A5', 'Delivery Due Date');
        $sheet->setCellValue('B5', optional($this->order->delivery_due_date)?->format('Y-m-d'));
        $sheet->setCellValue('A6', 'Contact Name');
        $sheet->setCellValue('B6', $this->order->contact_name);
        $sheet->setCellValue('A7', 'Contact Phone');
        $sheet->setCellValue('B7', $this->order->contact_phone);
        $sheet->setCellValue('A8', 'Currency');
        $sheet->setCellValue('B8', $this->order->currency);
        $sheet->setCellValue('A9', 'Total Amount');
        $sheet->setCellValue('B9', $this->order->total_amount);

        $headerRow = 11;
        $sheet->setCellValue('A' . $headerRow, 'Product');
        $sheet->setCellValue('B' . $headerRow, 'SKU');
        $sheet->setCellValue('C' . $headerRow, 'Quantity');
        $sheet->setCellValue('D' . $headerRow, 'Unit');
        $sheet->setCellValue('E' . $headerRow, 'Unit Price');
        $sheet->setCellValue('F' . $headerRow, 'Line Amount');
        $sheet->setCellValue('G' . $headerRow, 'Currency');
        $sheet->setCellValue('H' . $headerRow, 'Note');

        $row = $headerRow + 1;
        /** @var OrderItem $item */
        foreach ($this->order->items as $item) {
            $sheet->setCellValue('A' . $row, $item->product_name ?? $item->product?->name);
            $sheet->setCellValue('B' . $row, $item->product_sku ?? $item->product?->sku);
            $sheet->setCellValue('C' . $row, $item->quantity);
            $sheet->setCellValue('D' . $row, $item->unit);
            $sheet->setCellValue('E' . $row, $item->unit_price);
            $sheet->setCellValue('F' . $row, $item->line_amount);
            $sheet->setCellValue('G' . $row, $item->currency);
            $sheet->setCellValue('H' . $row, $item->note);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        if ($binary === false) {
            throw new RuntimeException('Failed to capture order print Excel content.');
        }

        return $binary;
    }
}
