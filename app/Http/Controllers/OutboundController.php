<?php

namespace App\Http\Controllers;

use App\Http\Resources\Inventory\CommonOutboundResource;
use App\Models\Outbound;
use App\Models\OutboundItem;
use App\Services\SnakeCaseData;
use Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutboundController extends Controller
{
    use SnakeCaseData;

    public function getOutbounds(): JsonResponse
    {
        $outbounds = Outbound::query()->with(['items', 'warehouse', 'customer'])->paginate();
        $jsonResponse = CommonOutboundResource::collection($outbounds);
        return $jsonResponse->response();
    }

    public function getOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function createOutbound(Request $request): JsonResponse
    {
        $form = $this->snakeCaseData($request->all());
        $model = Arr::only($form, [
            'outbound_order_id',
            'outbound_date',
            'carrier_name',
            'status',
        ]);
        $model['warehouse_id'] = $form['warehouse']['id'] ?? null;
        $model['customer_id'] = $form['customer']['id'] ?? null;
        $model['customer_contact_id'] = $form['customerContact']['id']?? null;
        $outbound = new Outbound($model);
        $outbound->save();
        $items = [];
        foreach ($form['items'] as $item) {
            $itemModel = Arr::only($item, [
                'quantity',
                'lot_number',
                'note',
                'inbound_item_id',
                'inventory_item_id',
            ]);
            $itemModel['product_id'] = $item['product']['id'] ?? null;

            $items[] = new OutboundItem($this->snakeCaseData($itemModel));
        }

        $outbound->items()->saveMany($items);
        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }

    public function updateOutbound(Request $request, $id): JsonResponse
    {
        return response()->json()->setStatusCode('500', 'not implemented');
    }

    public function deleteOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->find($id);
        $outbound->items()->delete();
        $outbound->delete();
        return response()->json()->setStatusCode('204');
    }

    public function approveOutbound($id): JsonResponse
    {
        $outbound = Outbound::query()->with(['items.product', 'warehouse', 'customer'])->find($id);
        if ($outbound->status!= 'pending') {
            return response()->json()->setStatusCode('400', 'outbound status must be pending to approve' );
        }
        $outbound->status = 'approved';
        $outbound->save();

        foreach ($outbound->items as $item) {
            $inventoryItem = $item->inventoryItem;

            $inventoryItem->left_quantity = $inventoryItem->left_quantity - $item->quantity;
            $inventoryItem->save();
        }

        $resource = new CommonOutboundResource($outbound);
        return $resource->response();
    }
}
