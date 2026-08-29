<?php

namespace App\Repositories;

use App\Models\CreditAnalysis;
use Illuminate\Contracts\Pagination\Paginator;

interface CreditAnalysisRepositoryInterface
{
    public function paginateContracted(int $perPage = 15): Paginator;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CreditAnalysis;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CreditAnalysis $analysis, array $data): CreditAnalysis;
}
