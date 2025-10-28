<?php

namespace App\Http\Controllers;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Requests\Procurement\CreateOrderRequest;
use App\Http\Requests\Procurement\ListOrdersRequest;
use App\Http\Requests\Procurement\UpdateOrderRequest;
use App\Http\Resources\Procurement\OrderResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

final class OrderController extends Controller
{
    public function getOrders(
        ListOrdersRequest $request,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $params = $request->validated();
        $itemsPerPage = (int) data_get($params, 'itemsPerPage', 20);
        $page = (int) data_get($params, 'page', 1);
        $orderNumber = data_get($params, 'orderNumber');
        $customerId = data_get($params, 'customerId');
        $status = data_get($params, 'status');
        $deliveryDueStart = data_get($params, 'deliveryDueStart');
        $deliveryDueEnd = data_get($params, 'deliveryDueEnd');

        $customerId = $customerId !== null ? (int) $customerId : null;
        $deliveryDueStart = $deliveryDueStart ? Carbon::parse($deliveryDueStart) : null;
        $deliveryDueEnd = $deliveryDueEnd ? Carbon::parse($deliveryDueEnd) : null;

        $orders = $orderService->getOrders(
            $itemsPerPage,
            $page,
            $orderNumber,
            $customerId,
            $status,
            $deliveryDueStart,
            $deliveryDueEnd,
        );

        $resources = OrderResource::collection($orders);

        return $resources->response();
    }

    public function createOrder(
        CreateOrderRequest $request,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->createOrder($request->validated());

        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function getOrder(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->getOrder($id);
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function updateOrder(
        int $id,
        UpdateOrderRequest $request,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->updateOrder($id, $request->validated());
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function cancelOrder(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->cancelOrder($id);
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function submitOrder(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->submitOrder($id);
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function sendOrder(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->sendOrder($id);
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function completeOrder(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        $order = $orderService->completeOrder($id);
        $resource = new OrderResource($order);

        return $resource->response();
    }

    public function cancelOrderAction(
        int $id,
        OrderServiceInterface $orderService,
    ): JsonResponse {
        return $this->cancelOrder($id, $orderService);
    }
}
