<?php

namespace Tests\Feature;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_redirects_when_the_analysis_cannot_be_viewed_in_simulation(): void
    {
        foreach ([AnalysisStatus::PENDING, AnalysisStatus::REJECTED] as $status) {
            $analysis = CreditAnalysis::factory()->create([
                'status' => $status,
            ]);

            $this->get("/simulacao/{$analysis->id}")
                ->assertRedirect('/')
                ->assertSessionHas('erro', 'Esta análise não está disponível para visualização.');
        }
    }
}
