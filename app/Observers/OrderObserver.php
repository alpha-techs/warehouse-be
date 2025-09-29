<?php

namespace App\Observers;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderObserver
{
    public function creating(Order $order): void
    {
        if (empty($order->order_number)) {
            $order->order_number = $this->generateOrderNumber();
        }

        if ($order->customer_id && empty($order->customer_name)) {
            $customer = Customer::find($order->customer_id);
            $order->customer_name = $customer?->name;
        }
    }

    public function updating(Order $order): void
    {
        if ($order->isDirty('customer_id')) {
            $customer = Customer::find($order->customer_id);
            $order->customer_name = $customer?->name;
        }
    }

    private function generateOrderNumber(): string
    {
        do {
            $orderNumber = sprintf(
                'PO-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(4))
            );
        } while (Order::whereOrderNumber($orderNumber)->exists());

        return $orderNumber;
    }
}
