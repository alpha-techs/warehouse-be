<?php

namespace App\Http\Resources\Inbound;

use App\Http\Resources\BaseResource;

class InboundReportResource extends BaseResource
{
    protected function compose(): array
    {
        $data = parent::compose();
        $reportDate = $this->resource->started_at?->format('Y-m-d');
        $createdAt = $this->resource->created_at;
        return [
            ...$data,
            'report_date' => $reportDate,
            'created_at' => $createdAt,
        ];
    }
}
