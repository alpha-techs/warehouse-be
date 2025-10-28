<?php

namespace App\Http\Resources\ExpressSampleShipment;

use App\Http\Resources\BaseResource;
use DateTimeInterface;

class ExpressSampleShipmentReportResource extends BaseResource
{
    protected function compose(): array
    {
        /** @var \App\Models\ExpressSampleShipmentReport $report */
        $report = $this->resource;

        $createdAt = $report->created_at instanceof DateTimeInterface
            ? $report->created_at->format('Y-m-d H:i:s')
            : $report->created_at;

        $completedAt = $report->completed_at instanceof DateTimeInterface
            ? $report->completed_at->format('Y-m-d H:i:s')
            : $report->completed_at;

        $expiresAt = $report->expires_at instanceof DateTimeInterface
            ? $report->expires_at->format('Y-m-d H:i:s')
            : $report->expires_at;

        return [
            'id' => $report->id,
            'express_sample_shipment_id' => $report->express_sample_shipment_id,
            'warehouse' => $report->warehouse ? [
                'id' => $report->warehouse->id,
                'name' => $report->warehouse->name,
            ] : null,
            'customer' => $report->customer ? [
                'id' => $report->customer->id,
                'name' => $report->customer->name,
            ] : null,
            'report_date' => $report->created_at?->format('Y-m-d'),
            'format' => $report->format,
            'status' => $report->status,
            'download_url' => $report->getDownloadUrl(),
            'error_message' => $report->error_message,
            'created_at' => $createdAt,
            'completed_at' => $completedAt,
            'expires_at' => $expiresAt,
        ];
    }
}

