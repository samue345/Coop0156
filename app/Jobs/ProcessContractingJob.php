<?php

namespace App\Jobs;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;


class ProcessContractingJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public CreditAnalysis $analysis)
    {
    }

    public function handle(): void
    {
        if ($this->analysis->status !== AnalysisStatus::PROCESSING_CONTRACT) {
            Log::info('Credit contracting job skipped because analysis is not processing.', [
                'analysis_id' => $this->analysis->getKey(),
                'status' => $this->analysis->status?->value,
            ]);

            return;
        }

        $this->analysis->update(['status' => AnalysisStatus::CONTRACTED]);

        Log::info('Credit contracting completed.', [
            'analysis_id' => $this->analysis->getKey(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        $this->analysis->update([
            'status' => AnalysisStatus::APPROVED,
        ]);

        Log::error('Credit contracting failed.', [
            'analysis_id' => $this->analysis->getKey(),
            'message' => $exception->getMessage(),
        ]);
    }
}
