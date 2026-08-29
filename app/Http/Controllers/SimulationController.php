<?php

namespace App\Http\Controllers;

use App\Models\CreditAnalysis;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SimulationController extends Controller
{
    public function show(CreditAnalysis $creditAnalysis): View|RedirectResponse
    {
        if (!$creditAnalysis->status->canBeViewedInSimulation()) {
            return redirect('/')->with('erro', 'Esta análise não está disponível para visualização.');
        }

        return view('simulation', ['analise' => $creditAnalysis]);
    }
}
