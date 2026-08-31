<?php

namespace App\Repositories;

use App\Models\CreditAnalysis;

interface CreditAnalysisRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): CreditAnalysis;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(CreditAnalysis $analysis, array $data): CreditAnalysis;
}
