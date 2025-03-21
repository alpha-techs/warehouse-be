<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\UpsertInboundRequest;
use App\Http\Resources\Inventory\CommonInboundResource;
use App\Models\Inbound;
use App\Models\InboundItem;
use App\Models\InventoryItem;
use App\Services\SnakeCaseData;
use Arr;
use Illuminate\Http\JsonResponse;

class InboundController extends Controller
{
    use SnakeCaseData;

    public function getInbounds(): JsonResponse
    {
        $inbounds = Inbound::query()->with(['items', 'warehouse', 'customer'])->paginate();
        $jsonResponse = CommonInboundResource::collection($inbounds);
        return $jsonResponse->response();
    }

    public function getInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function createInbound(UpsertInboundRequest $request): JsonResponse
    {
        $form = $this->snakeCaseData($request->all());
        $model = Arr::only($form, [
            'inbound_order_id',
            'inbound_date',
            'status',
        ]);

        $model['warehouse_id'] = $form['warehouse']['id'] ?? null;
        $model['customer_id'] = $form['customer']['id'] ?? null;
        $model['customer_contact_id'] = $form['customerContact']['id']?? null;

        $inbound = new Inbound($model);
        $inbound->save();

        $items = [];
        foreach ($form['items'] as $item) {
            $itemModel = Arr::only($item, [
                'quantity',
                'per_item_weight',
                'per_item_weight_unit',
                'total_weight',
                'manufacture_date',
                'best_before_date',
                'lot_number',
                'ship_name',
            ]);

            $itemModel['product_id'] = $item['product']['id'] ?? null;

            $items[] = new InboundItem($this->snakeCaseData($itemModel));
        }

        $inbound->items()->saveMany($items);

        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }

    public function updateInbound($id, UpsertInboundRequest $request): JsonResponse
    {
        return response()->json()->setStatusCode('500', 'not implemented');
    }

    public function deleteInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->find($id);
        $inbound->items()->delete();
        $inbound->delete();
        return response()->json()->setStatusCode('204');
    }

    public function approveInbound($id): JsonResponse
    {
        $inbound = Inbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($inbound->status != 'pending') {
            return response()->json()->setStatusCode('400', 'inbound status must be pending to approve' );
        }
        $inbound->status = 'approved';
        $inbound->save();

        foreach ($inbound->items as $inboundItem) {
            $inventoryItemModel = [
                'warehouse_id' => $inbound['warehouse']['id'] ?? null,
                'customer_id' => $inbound['customer']['id'] ?? null,
                'inbound_order_id' => $inbound['inbound_order_id'] ?? null,
                'inbound_id' => $inbound['id'],
                'inbound_item_id' => $inboundItem['id'],
                'inbound_date' => $inbound['inbound_date'] ?? null,
                'product_id' => $inboundItem['product']['id'] ?? null,
                'per_item_weight' => $inboundItem['per_item_weight'] ?? null,
                'per_item_weight_unit' => $inboundItem['per_item_weight_unit'] ?? null,
                'total_weight' => $inboundItem['total_weight'] ?? null,
                'manufacture_date' => $inboundItem['manufacture_date'] ?? null,
                'best_before_date' => $inboundItem['best_before_date'] ?? null,
                'lot_number' => $inboundItem['lot_number'] ?? null,
                'ship_name' => $inboundItem['ship_name'] ?? null,
                'inbound_quantity' => $inboundItem['quantity'] ?? null,
                'left_quantity' => $inboundItem['quantity'] ?? null,
                'left_sub_quantity' => 0,
            ];
            $inventoryItem = new InventoryItem($inventoryItemModel);
            $inventoryItem->save();
        }

        $resource = new CommonInboundResource($inbound);
        return $resource->response();
    }
}
