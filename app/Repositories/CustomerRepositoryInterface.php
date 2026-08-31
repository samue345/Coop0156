<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\Paginator;

interface CustomerRepositoryInterface
{
    public function paginate(): Paginator;

    public function firstOrCreateByCpf(string $cpf, array $data): Customer;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;
}
