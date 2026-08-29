<?php

namespace App\Repositories;

use App\Models\CreditAnalysis;
use Illuminate\Contracts\Pagination\Paginator;

interface CreditAnalysisRepositoryInterface
{
    public function paginateContracts(int $perPage = 15): Paginator;

    public function create(array $data): CreditAnalysis;

    public function update(CreditAnalysis $analysis, array $data): CreditAnalysis;
}
