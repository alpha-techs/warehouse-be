<?php

namespace App\Services;

use App\Contracts\Services\CustomerServiceInterface;
use App\Models\Customer;
use Illuminate\Contracts\Pagination\Paginator;

class CustomerService implements CustomerServiceInterface
{
    public function getCustomers(int $itemsPerPage = 30, int $page = 1, ?string $name = null): Paginator
    {
        $query = Customer::query();
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }

    public function getCustomer(int $id): Customer
    {
        return Customer::find($id);
    }

    public function createCustomer(array $data): Customer
    {
        $contactDta = $data['contact'] ?? null;
        unset($data['contact']);
        $customer = new Customer($data);
        $customer->save();
        if ($contactDta) {
            $customer->contact()->create($contactDta);
        }
        return $customer;
    }

    public function updateCustomer(int $id, array $data): Customer
    {
        $customer = Customer::find($id);
        $contactDta = $data['contact'] ?? null;
        unset($data['contact']);
        $customer->update($data);
        if ($customer->contact) {
            $customer->contact()->update($contactDta);
        } else {
            if ($contactDta) {
                $customer->contact()->create($contactDta);
            }
        }
        return $customer;
    }

    public function deleteCustomer(int $id): bool
    {
        $customer = Customer::find($id);
        return $customer->delete();
    }
}
