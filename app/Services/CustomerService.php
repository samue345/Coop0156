<?php

namespace App\Services;

use App\Models\Customer;
use App\Repositories\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers
    ) {
    }

    public function list(int $perPage = 15): Paginator
    {
        return $this->customers->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer
    {
        return $this->customers->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        return $this->customers->update($customer, $data);
    }

    public function delete(Customer $customer): void
    {
        $this->customers->delete($customer);
    }
}
