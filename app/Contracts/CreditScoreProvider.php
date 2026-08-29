<?php

namespace App\Contracts;

interface CreditScoreProvider
{
    public function scoreFor(string $cpf): int;
}
