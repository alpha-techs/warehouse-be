<?php

namespace App\Http\Controllers;

use App\Contracts\Services\InventoryServiceInterface;
use App\Http\Requests\Inventory\GetInventoryListRequest;
use App\Http\Resources\Inventory\CommonInventoryResource;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function getList(
        GetInventoryListRequest $request,
        InventoryServiceInterface $inventoryService,
    ): JsonResponse
    {
        $itemsPerPage = intval($request->validated('itemsPerPage', 30));
        $page = intval($request->validated('page', 1));

        $items = $inventoryService->getInventoryList($itemsPerPage, $page);
        $resources = CommonInventoryResource::collection($items);

        return $resources->response();
    }
}
