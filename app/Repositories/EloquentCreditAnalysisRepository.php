<?php

namespace App\Repositories;

use App\Models\CreditAnalysis;

class EloquentCreditAnalysisRepository implements CreditAnalysisRepositoryInterface
{
    public function create(array $data): CreditAnalysis
    {
        return CreditAnalysis::query()->create($data);
    }

    public function update(CreditAnalysis $analysis, array $data): CreditAnalysis
    {
        $analysis->update($data);

        return $analysis->refresh();
    }
}
