<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Jobs\ProcessContractingJob;
use App\Models\CreditAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ProcessContractingJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_contracts_a_processing_analysis_and_logs_success(): void
    {
        $analysis = CreditAnalysis::factory()->create([
            'status' => AnalysisStatus::PROCESSING_CONTRACT,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Credit contracting completed.', Mockery::on(
                fn (array $context) => $context['analysis_id'] === $analysis->id
            ));

        (new ProcessContractingJob($analysis))->handle();

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::CONTRACTED->value,
        ]);
    }

    public function test_it_skips_analysis_that_is_not_processing_and_logs_the_reason(): void
    {
        $analysis = CreditAnalysis::factory()->create([
            'status' => AnalysisStatus::APPROVED,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Credit contracting job skipped because analysis is not processing.', Mockery::on(
                fn (array $context) => $context['analysis_id'] === $analysis->id
                    && $context['status'] === AnalysisStatus::APPROVED->value
            ));

        (new ProcessContractingJob($analysis))->handle();

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }

    public function test_it_restores_the_analysis_model_after_serialization(): void
    {
        $analysis = CreditAnalysis::factory()->create([
            'status' => AnalysisStatus::PROCESSING_CONTRACT,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Credit contracting completed.', Mockery::on(
                fn (array $context) => $context['analysis_id'] === $analysis->id
            ));

        /** @var ProcessContractingJob $job */
        $job = unserialize(serialize(new ProcessContractingJob($analysis)));
        $job->handle();

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::CONTRACTED->value,
        ]);
    }

    public function test_failed_reverts_analysis_to_approved_and_logs_the_error(): void
    {
        $analysis = CreditAnalysis::factory()->create([
            'status' => AnalysisStatus::PROCESSING_CONTRACT,
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with('Credit contracting failed.', Mockery::on(
                fn (array $context) => $context['analysis_id'] === $analysis->id
                    && $context['message'] === 'Queue crashed'
            ));

        (new ProcessContractingJob($analysis))->failed(new RuntimeException('Queue crashed'));

        $this->assertDatabaseHas('analises_credito', [
            'id' => $analysis->id,
            'status' => AnalysisStatus::APPROVED->value,
        ]);
    }
}
