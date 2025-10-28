<?php

namespace App\Http\Resources\Procurement;

use App\Contracts\Models\OrderStatus;
use App\Http\Resources\BaseResource;

final class OrderPrintCreatedResource extends BaseResource
{
    protected function compose(): array
    {
        $print = $this->resource;
        $order = $print->order;

        $orderData = null;
        if ($order) {
            $status = $order->status instanceof OrderStatus ? $order->status->value : $order->status;

            $orderData = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $status,
            ];
        }

        return [
            'print_id' => $print->id,
            'order' => $orderData,
            'format' => $print->format,
            'expires_at' => $print->expires_at,
        ];
    }
}
