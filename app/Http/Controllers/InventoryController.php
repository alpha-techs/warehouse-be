<?php

namespace App\Http\Controllers;

use App\Http\Resources\Inventory\CommonInventoryResource;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function getList(Request $request): JsonResponse
    {
        $query = InventoryItem::query()->with(['warehouse', 'customer', 'product']);
        $warehouseId = $request->get('warehouse_id');
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        $customerId = $request->get('customer_id');
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        $productId = $request->get('product_id');
        if ($productId) {
            $query->where('product_id', $productId);
        }
        $items = $query->paginate();

        $resources = CommonInventoryResource::collection($items);
        return $resources->response();
    }
}
