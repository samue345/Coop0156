<?php

namespace App\Http\Controllers;

use App\Repositories\CreditAnalysisRepositoryInterface;
use Illuminate\Contracts\View\View;

class ContractedCreditController extends Controller
{
    public function __construct(
        private readonly CreditAnalysisRepositoryInterface $analyses,
    ) {
    }

    public function index(): View
    {
        return view('contracts.index', [
            'contracts' => $this->analyses->paginateContracted(),
        ]);
    }
}

