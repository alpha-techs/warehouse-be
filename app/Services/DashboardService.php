<?php

namespace App\Services;

use App\Contracts\Models\InboundStatus;
use App\Contracts\Models\OutboundStatus;
use App\Contracts\Services\DashboardServiceInterface;
use App\Models\Customer;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\InventoryItem;
use App\Models\Outbound;
use App\Models\OutboundItem;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;

final class DashboardService implements DashboardServiceInterface
{

    public function getStats(): array
    {
        $totalInventoryCount = $this->getTotalInventoryCount();
        $monthlyInboundCount = $this->getMonthlyInboundCount();
        $monthlyOutboundCount = $this->getMonthlyOutboundCount();
        $pendingInboundCount = $this->getPendingInboundCount();
        $pendingOutboundCount = $this->getPendingOutboundCount();
        $warehouseCount = $this->getWarehouseCount();
        $customerCount = $this->getCustomerCount();
        $productCount = $this->getProductCount();

        return [
            'totalInventoryCount' => $totalInventoryCount,
            'monthlyInboundCount' => $monthlyInboundCount,
            'monthlyOutboundCount' => $monthlyOutboundCount,
            'pendingInboundCount' => $pendingInboundCount,
            'pendingOutboundCount' => $pendingOutboundCount,
            'warehouseCount' => $warehouseCount,
            'customerCount' => $customerCount,
            'productCount' => $productCount,
        ];
    }

    private function getTotalInventoryCount(): int
    {
        $query = InventoryItem::query();
        return $query->sum('left_quantity');
    }

    private function getMonthlyInboundCount(): int
    {
        $query = InboundItem::query()
            ->where('inbound_date', '>=', Carbon::now()->startOfMonth()->toDateString())
            ->whereInboundStatus(InboundStatus::APPROVED);
        return $query->sum('quantity');
    }

    private function getMonthlyOutboundCount(): int
    {
        $query = OutboundItem::query()
            ->where('outbound_date', '>=', Carbon::now()->startOfMonth()->toDateString())
            ->whereOutboundStatus(OutboundStatus::APPROVED);
        return $query->sum('quantity');
    }

    private function getPendingInboundCount(): int
    {
        $query = Inbound::query()
            ->whereStatus(InboundStatus::PENDING);
        return $query->count();
    }

    private function getPendingOutboundCount(): int
    {
        $query = Outbound::query()
            ->whereStatus(OutboundStatus::PENDING);
        return $query->count();
    }

    private function getWarehouseCount(): int
    {
        $query = Warehouse::query();
        return $query->count();
    }

    private function getCustomerCount(): int
    {
        $query = Customer::query();
        return $query->count();
    }

    private function getProductCount(): int
    {
        $query = Product::query();
        return $query->count();
    }

}
