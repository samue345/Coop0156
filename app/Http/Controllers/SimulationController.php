<?php

namespace App\Http\Controllers;

use App\Enums\AnalysisStatus;
use App\Models\CreditAnalysis;

class SimulationController extends Controller
{
    /**
     * Exibe a tela de simulação/detalhes de uma análise aprovada.
     *
     * GET /simulacao/{id}
     *
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        $creditAnalysis = CreditAnalysis::findOrFail($id);

        // Só exibe a simulação para análises aprovadas
        if ($creditAnalysis->status !== AnalysisStatus::APPROVED) {
            return redirect('/')->with('erro', 'Esta análise não está disponível para simulação.');
        }

        $analise = $creditAnalysis;

        return view('simulacao', compact('analise'));
    }
}
