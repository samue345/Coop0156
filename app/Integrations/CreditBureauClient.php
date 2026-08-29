<?php

namespace App\Integrations;

use App\Exceptions\CreditBureauException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
final class CreditBureauClient
{

    public function scoreFor(string $cpf): int
    {
        try {
            $response = Http::timeout(config('services.score_bureau.timeout', 3))
                ->get(
                rtrim((string) config('services.score_bureau.url'), '/') . "/{$cpf}"
            );

            $response->throw();
        }
        catch (ConnectionException) {
            throw new CreditBureauException(
                'Não foi possível consultar o Bureau de Crédito.',
                504
            );
        }
        catch (RequestException) {
            throw new CreditBureauException(
                'O Bureau de Crédito está indisponível.',
                502
            );
        }

        $score = $response->json('score');

        if (!is_int($score)) {
            throw new CreditBureauException(
                'O Bureau de Crédito retornou uma resposta inválida.',
                502
            );
        }

        return $score;
    }
}
