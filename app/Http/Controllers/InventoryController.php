<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InventoryServiceInterface;
use App\Http\Requests\Inventory\GetInventoryListRequest;
use App\Http\Resources\Inventory\CommonInventoryResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class InventoryController extends Controller
{
    public function getList(
        GetInventoryListRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $params = $request->validated();
        $itemsPerPage = data_get($params, 'itemsPerPage', 30);
        $page = data_get($params, 'page', 1);
        $lotNumber = data_get($params, 'lotNumber');
        $warehouseId = data_get($params, 'warehouseId');
        $productId = data_get($params, 'productId');
        $inboundDateFrom = data_get($params, 'inboundDateFrom');
        $inboundDateFrom = $inboundDateFrom ? Carbon::parse($inboundDateFrom) : null;
        $inboundDateTo = data_get($params, 'inboundDateTo');
        $inboundDateTo = $inboundDateTo ? Carbon::parse($inboundDateTo) : null;

        $items = $inventoryService->getInventoryList(
            $itemsPerPage,
            $page,
            $lotNumber,
            $warehouseId,
            $productId,
            $inboundDateFrom,
            $inboundDateTo
        );
        $resources = CommonInventoryResource::collection($items);
        return $resources->response();
    }
}
