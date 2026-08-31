<?php

namespace App\Http\Controllers;

use App\Services\ContractedCreditService;
use Illuminate\Contracts\View\View;

class ContractedCreditController extends Controller
{
    public function __construct(
        private readonly ContractedCreditService $contracts,
    ) {
    }

    public function index(): View
    {
        return view('contracts.index', ['contracts' => $this->contracts->paginate()]);
    }
}
