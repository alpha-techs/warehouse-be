<?php

namespace App\Http\Controllers;

use App\Contracts\Services\CustomerServiceInterface;
use App\Http\Requests\Customer\GetCustomersRequest;
use App\Http\Requests\Customer\UpsertCustomerRequest;
use App\Http\Resources\Customer\CommonCustomerResource;
use Illuminate\Http\JsonResponse;

final class CustomerController extends Controller
{
    public function getCustomers(
        GetCustomersRequest $request,
        CustomerServiceInterface $customerService,
    ): JsonResponse
    {
        $itemsPerPage = intval($request->validated('itemsPerPage', 30));
        $page = intval($request->validated('page', 1));
        $name = $request->validated('name');

        $customers = $customerService->getCustomers($itemsPerPage, $page, $name);
        $jsonResponse = CommonCustomerResource::collection($customers);

        return $jsonResponse->response();
    }

    public function getCustomer(
        $id,
        CustomerServiceInterface $customerService,
    ): JsonResponse
    {
        $customer = $customerService->getCustomer($id);
        $resource = new CommonCustomerResource($customer);
        return $resource->response();
    }

    public function createCustomer(
        UpsertCustomerRequest $request,
        CustomerServiceInterface $customerService,
    ): JsonResponse
    {
        $form = $request->validated();

        $customer = $customerService->createCustomer($form);
        $jsonResponse = CommonCustomerResource::make($customer);

        return $jsonResponse->response();
    }

    public function updateCustomer(
        $id,
        UpsertCustomerRequest $request,
        CustomerServiceInterface $customerService,
    ): JsonResponse
    {
        $form = $request->validated();

        $customer = $customerService->updateCustomer($id, $form);
        $jsonResponse = CommonCustomerResource::make($customer);

        return $jsonResponse->response();
    }

    public function deleteCustomer(
        $id,
        CustomerServiceInterface $customerService,
    ): JsonResponse
    {
        $customerService->deleteCustomer($id);
        return response()->json()->setStatusCode(204);
    }
}
