<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;

class SimulationController extends Controller
{
    public function show(CreditAnalysis $creditAnalysis)
    {
        $availableStatuses = [
            AnalysisStatus::APPROVED,
            AnalysisStatus::CONTRACTED,
        ];

        if (! in_array($creditAnalysis->status, $availableStatuses, true)) {
            return redirect('/')->with('erro', 'Esta análise não está disponível para visualização.');
        }

        $analise = $creditAnalysis;

        return view('simulation', compact('analise'));
    }
}

