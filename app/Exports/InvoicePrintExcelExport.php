<?php

namespace App\Exports;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePrint;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');

        $sheet->setCellValue('A1', 'Invoice Print');
        $sheet->setCellValue('A2', 'Invoice Number');
        $sheet->setCellValue('B2', $this->invoice->invoice_number);
        $sheet->setCellValue('A3', 'Customer');
        $sheet->setCellValue('B3', $this->invoice->customer_name ?? $this->invoice->customer?->name);
        $sheet->setCellValue('A4', 'Issue Date');
        $sheet->setCellValue('B4', optional($this->invoice->issue_date)?->format('Y-m-d H:i'));
        $sheet->setCellValue('A5', 'Due Date');
        $sheet->setCellValue('B5', optional($this->invoice->due_date)?->format('Y-m-d'));
        $sheet->setCellValue('A6', 'Currency');
        $sheet->setCellValue('B6', $this->invoice->currency);
        $sheet->setCellValue('A7', 'Subtotal');
        $sheet->setCellValue('B7', $this->invoice->subtotal_amount);
        $sheet->setCellValue('A8', 'Tax');
        $sheet->setCellValue('B8', $this->invoice->tax_amount);
        $sheet->setCellValue('A9', 'Total');
        $sheet->setCellValue('B9', $this->invoice->total_amount);

        $headerRow = 11;
        $sheet->setCellValue('A' . $headerRow, 'Item');
        $sheet->setCellValue('B' . $headerRow, 'Quantity');
        $sheet->setCellValue('C' . $headerRow, 'Unit Price');
        $sheet->setCellValue('D' . $headerRow, 'Line Amount');
        $sheet->setCellValue('E' . $headerRow, 'Tax Amount');
        $sheet->setCellValue('F' . $headerRow, 'Note');

        $row = $headerRow + 1;
        /** @var InvoiceItem $item */
        foreach ($this->invoice->items as $item) {
            $sheet->setCellValue('A' . $row, $item->product_name);
            $sheet->setCellValue('B' . $row, $item->quantity);
            $sheet->setCellValue('C' . $row, $item->unit_price);
            $sheet->setCellValue('D' . $row, $item->line_amount);
            $sheet->setCellValue('E' . $row, $item->tax_amount);
            $sheet->setCellValue('F' . $row, $item->note);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        if ($binary === false) {
            throw new RuntimeException('Failed to capture invoice print Excel content.');
        }

        return $binary;
    }
}
