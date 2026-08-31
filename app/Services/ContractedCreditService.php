<?php

namespace App\Services;

use App\Repositories\CreditAnalysisRepositoryInterface;
use Illuminate\Contracts\Pagination\Paginator;

class ContractedCreditService
{
    public function __construct(
        private readonly CreditAnalysisRepositoryInterface $analyses,
    ) {
    }

    public function paginate(): Paginator
    {
        return $this->analyses->paginateContracts();
    }
}
