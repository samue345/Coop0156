<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\Paginator;

interface CustomerRepositoryInterface
{
    public function paginate(int $perPage = 15): Paginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function firstOrCreateByCpf(string $cpf, array $data): Customer;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Customer;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): void;
}
