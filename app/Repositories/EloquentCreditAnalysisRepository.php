<?php

namespace App\Repositories;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use App\Support\Pagination;
use Illuminate\Contracts\Pagination\Paginator;

class EloquentCreditAnalysisRepository implements CreditAnalysisRepositoryInterface
{
    public function paginateContracts(): Paginator
    {
        return CreditAnalysis::query()
            ->with('customer')
            ->whereIn('status', [
                AnalysisStatus::PROCESSING_CONTRACT->value,
                AnalysisStatus::CONTRACTED->value,
            ])
            ->latest('updated_at')
            ->simplePaginate(Pagination::CONTRACTS_PER_PAGE);
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
