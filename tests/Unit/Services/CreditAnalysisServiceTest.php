<?php

namespace Tests\Unit\Services;

use App\Contracts\CreditScoreProvider;
use App\Domain\CreditAnalysis\CreditEligibilityEvaluator;
use App\Jobs\ProcessContractingJob;
use App\Models\CreditAnalysis;
use App\Repositories\CreditAnalysisRepositoryInterface;
use App\Repositories\CustomerRepositoryInterface;
use App\Services\CreditAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class CreditAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_does_not_dispatch_contracting_when_the_atomic_transition_fails(): void
    {
        Queue::fake();

        $analysis = CreditAnalysis::factory()->create();
        $analyses = Mockery::mock(CreditAnalysisRepositoryInterface::class);
        $analyses
            ->expects('transitionToProcessingContract')
            ->once()
            ->with($analysis)
            ->andReturnFalse();

        $service = new CreditAnalysisService(
            $analyses,
            Mockery::mock(CustomerRepositoryInterface::class),
            Mockery::mock(CreditScoreProvider::class),
            new CreditEligibilityEvaluator([]),
        );

        $this->assertNull($service->startContracting($analysis));
        Queue::assertNothingPushed();
    }
}
