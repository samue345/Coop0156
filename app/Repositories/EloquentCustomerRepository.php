<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Support\Pagination;
use Illuminate\Contracts\Pagination\Paginator;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function paginate(): Paginator
    {
        return Customer::query()
            ->latest('id')
            ->simplePaginate(Pagination::CUSTOMERS_PER_PAGE);
    }

    public function firstOrCreateByCpf(string $cpf, array $data): Customer
    {
        return Customer::query()->firstOrCreate(['cpf' => $cpf], $data);
    }

    public function create(array $data): Customer
    {
        return Customer::query()->create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }
}
