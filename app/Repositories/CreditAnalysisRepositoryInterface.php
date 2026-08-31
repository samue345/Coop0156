<?php

namespace App\Repositories;

use App\Models\CreditAnalysis;
use Illuminate\Contracts\Pagination\Paginator;

interface CreditAnalysisRepositoryInterface
{
    public function paginateContracts(): Paginator;

    public function create(array $data): CreditAnalysis;

    public function update(CreditAnalysis $analysis, array $data): CreditAnalysis;

    public function transitionToProcessingContract(CreditAnalysis $analysis): bool;
}
