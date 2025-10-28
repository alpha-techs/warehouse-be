<?php

namespace App\Http\Resources\Procurement;

use App\Contracts\Models\OrderStatus;
use App\Http\Resources\BaseResource;

final class OrderPrintResource extends BaseResource
{
    protected function compose(): array
    {
        $print = $this->resource;
        $order = $print->order;

        $orderData = null;
        if ($order) {
            $status = $order->status instanceof OrderStatus ? $order->status->value : $order->status;
            $customer = $order->customer;

            $orderData = [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $status,
                'customer' => $customer ? [
                    'id' => $customer->id,
                    'name' => $customer->name,
                ] : null,
                'delivery_due_date' => $order->delivery_due_date,
                'contact_name' => $order->contact_name,
                'contact_phone' => $order->contact_phone,
            ];
        }

        return [
            'id' => $print->id,
            'order' => $orderData,
            'format' => $print->format,
            'status' => $print->status,
            'download_url' => $print->getDownloadUrl(),
            'error_message' => $print->error_message,
            'created_at' => $print->created_at,
            'completed_at' => $print->completed_at,
            'expires_at' => $print->expires_at,
        ];
    }
}
