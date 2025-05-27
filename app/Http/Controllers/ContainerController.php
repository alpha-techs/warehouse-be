<?php

namespace App\Http\Controllers;

use App\Contracts\Models\ContainerStatus;
use App\Contracts\Services\ContainerServiceInterface;
use App\Http\Requests\Container\GetContainersRequest;
use App\Http\Requests\Container\UpsertContainerRequest;
use App\Http\Resources\Container\CommonContainerResource;
use Arr;
use Illuminate\Http\JsonResponse;

final class ContainerController extends Controller
{
    public function getContainers(
        GetContainersRequest $request,
        ContainerServiceInterface $containerService,
    ): JsonResponse
    {
        $itemsPerPage = intval($request->validated('itemsPerPage', 30));
        $page = intval($request->validated('page', 1));
        $containerNumber = $request->validated('containerNumber');
        $statuses = $request->validated('statuses');

        $containers = $containerService->getContainers($itemsPerPage, $page, $containerNumber, $statuses);
        $resources = CommonContainerResource::collection($containers);

        return $resources->response();
    }

    public function getContainer(
        $id,
        ContainerServiceInterface $containerService,
    ): JsonResponse
    {
        $container = $containerService->getContainer($id);
        $resource = new CommonContainerResource($container);
        return $resource->response();
    }

    public function createContainer(
        UpsertContainerRequest $request,
        ContainerServiceInterface $containerService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
            'containerNumber',
            'shippingLine',
            'vesselName',
            'voyageNumber',
            'arrivalDate',
            'clearanceDate',
            'dischargeDate',
            'returnDate',
            'status',
        ]);

        if (empty(Arr::get($data, 'status'))) {
            $data['status'] = ContainerStatus::SHIPPING;
        }

        foreach ($formData['items'] ?? [] as $item) {
            $itemData = Arr::only($item, [
                'quantity',
                'manufactureDate',
                'bestBeforeDate',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $itemData['productName'] = Arr::get($item, 'product.name');
            $data['items'][] = $itemData;
        }

        $container = $containerService->createContainer($data);
        $resource = new CommonContainerResource($container);
        return $resource->response();
    }

    public function updateContainer(
        $id,
        UpsertContainerRequest $request,
        ContainerServiceInterface $containerService,
    ): JsonResponse
    {
        $formData = $request->validated();

        $data = Arr::only($formData, [
            'containerNumber',
            'shippingLine',
            'vesselName',
            'voyageNumber',
            'arrivalDate',
            'clearanceDate',
            'dischargeDate',
            'returnDate',
            'status',
        ]);

        foreach ($formData['items'] ?? [] as $item) {
            $itemData = Arr::only($item, [
                'id',
                'quantity',
                'manufactureDate',
                'bestBeforeDate',
            ]);
            $itemData['productId'] = Arr::get($item, 'product.id');
            $itemData['productName'] = Arr::get($item, 'product.name');
            $data['items'][] = $itemData;
        }

        $container = $containerService->updateContainer($id, $data);
        $resource = new CommonContainerResource($container);
        return $resource->response();
    }

    public function deleteContainer(
        $id,
        ContainerServiceInterface $containerService,
    ): JsonResponse
    {
        $containerService->deleteContainer($id);
        return response()->json()->setStatusCode(204);
    }
}
