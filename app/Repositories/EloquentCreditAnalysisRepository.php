<?php

namespace App\Repositories;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use Illuminate\Contracts\Pagination\Paginator;

class EloquentCreditAnalysisRepository implements CreditAnalysisRepositoryInterface
{
    public function paginateContracts(int $perPage = 15): Paginator
    {
        return CreditAnalysis::query()
            ->with('customer')
            ->whereIn('status', [
                AnalysisStatus::PROCESSING_CONTRACT->value,
                AnalysisStatus::CONTRACTED->value,
            ])
            ->latest('updated_at')
            ->simplePaginate($perPage);
    }

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
