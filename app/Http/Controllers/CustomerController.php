<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customers
    ) {
    }

    public function index(): AnonymousResourceCollection
    {
        return CustomerResource::collection($this->customers->list());
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->customerData());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): JsonResponse
    {
        return (new CustomerResource($customer))->response();
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customers->update($customer, $request->customerData());

        return (new CustomerResource($customer))->response();
    }

    public function destroy(Customer $customer): Response
    {
        $this->customers->delete($customer);

        return response()->noContent();
    }
}
