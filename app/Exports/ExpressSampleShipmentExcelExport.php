<?php

namespace App\Exports;

use App\Models\ExpressSampleShipment;
use App\Models\ExpressSampleShipmentItem;
use App\Models\ExpressSampleShipmentReport;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $this->fillHeader($sheet);
        $this->fillItems($sheet);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $binary = ob_get_clean();

        if ($binary === false) {
            throw new RuntimeException('Failed to render express sample shipment Excel content.');
        }

        return $binary;
    }

    private function fillHeader(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'Express Sample Shipment Report');
        $sheet->setCellValue('A2', 'Report ID');
        $sheet->setCellValue('B2', $this->report->id);
        $sheet->setCellValue('A3', 'Shipment ID');
        $sheet->setCellValue('B3', $this->shipment->id);
        $sheet->setCellValue('A4', 'Order Number');
        $sheet->setCellValue('B4', $this->shipment->express_sample_order_id);
        $sheet->setCellValue('A5', 'Customer');
        $sheet->setCellValue('B5', $this->shipment->customer?->name);
        $sheet->setCellValue('C5', 'Warehouse');
        $sheet->setCellValue('D5', $this->shipment->warehouse?->name);
        $sheet->setCellValue('A6', 'Requested Ship Date');
        $sheet->setCellValue('B6', $this->shipment->requested_ship_date);
        $sheet->setCellValue('C6', 'Desired Delivery Date');
        $sheet->setCellValue('D6', $this->shipment->desired_delivery_date);
        $sheet->setCellValue('A7', 'Recipient');
        $sheet->setCellValue('B7', $this->shipment->recipient_name);
        $sheet->setCellValue('C7', 'Phone');
        $sheet->setCellValue('D7', $this->shipment->recipient_phone_number);
        $sheet->setCellValue('A8', 'Address');
        $address = implode(' ', array_filter([
            $this->shipment->recipient_postal_code,
            $this->shipment->recipient_prefecture,
            $this->shipment->recipient_city,
            $this->shipment->recipient_address_line1,
            $this->shipment->recipient_address_line2,
        ]));
        $sheet->setCellValue('B8', $address);
    }

    private function fillItems(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): void
    {
        $startRow = 10;
        $headers = [
            'Product Name',
            'SKU',
            'Lot Number',
            'Inbound No',
            'Quantity',
            'Unit',
            'Sample Packaging',
            'Note',
        ];

        $sheet->fromArray($headers, null, 'A' . $startRow);

        /** @var ExpressSampleShipmentItem[] $items */
        $items = $this->shipment->items ?? [];
        $currentRow = $startRow + 1;
        foreach ($items as $item) {
            $sheet->setCellValue('A' . $currentRow, $item->product?->name);
            $sheet->setCellValue('B' . $currentRow, $item->product?->sku);
            $sheet->setCellValue('C' . $currentRow, $item->lot_number);
            $sheet->setCellValue('D' . $currentRow, $item->inbound_no);
            $sheet->setCellValue('E' . $currentRow, $item->quantity);
            $sheet->setCellValue('F' . $currentRow, $item->quantity_unit);
            $sheet->setCellValue('G' . $currentRow, $item->sample_packaging);
            $sheet->setCellValue('H' . $currentRow, $item->note);
            $currentRow++;
        }

        // Autosize columns for readability
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
    }
}

