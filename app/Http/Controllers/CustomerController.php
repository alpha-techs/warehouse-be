<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\UpsertCustomerRequest;
use App\Http\Resources\Customer\CommonCustomerResource;
use App\Models\Customer;
use App\Services\SnakeCaseData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    use SnakeCaseData;

    public function getCustomers(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->has('name')) {
            $query->where('name', 'like', '%'. $request->get('name'). '%');
        }
        $customers = $query->paginate();
        $jsonResponse = CommonCustomerResource::collection($customers);
        return $jsonResponse->response();
    }

    public function getCustomer($id): JsonResponse
    {
        $customer = Customer::query()->find($id);
        $resource = new CommonCustomerResource($customer);
        return $resource->response();
    }

    public function createCustomer(UpsertCustomerRequest $request): JsonResponse
    {
        $form = $request->all();

        $form['postalCode'] = $form['address']['postalCode'] ?? null;
        $form['detailAddress1'] = $form['address']['detailAddress1'] ?? null;
        $form['detailAddress2'] = $form['address']['detailAddress2'] ?? null;
        unset($form['address']);
        unset($form['contacts']);
        $customer = new Customer($this->snakeCaseData($form));
        $customer->save();
        $jsonResponse = CommonCustomerResource::make($customer);
        return $jsonResponse->response();
    }

    public function updateCustomer(UpsertCustomerRequest $request, $id): JsonResponse
    {
        $form = $request->all();
        $form['postalCode'] = $form['address']['postalCode'];
        $form['detailAddress1'] = $form['address']['detailAddress1'];
        $form['detailAddress2'] = $form['address']['detailAddress2'];
        unset($form['address']);
        unset($form['contacts']);
        $customer = Customer::query()->find($id);
        $customer->update($this->snakeCaseData($form));
        $jsonResponse = CommonCustomerResource::make($customer);
        return $jsonResponse->response();
    }

    public function deleteCustomer($id): JsonResponse
    {
        $customer = Customer::query()->find($id);
        $customer->delete();
        return response()->json()->setStatusCode(204);
    }
}
