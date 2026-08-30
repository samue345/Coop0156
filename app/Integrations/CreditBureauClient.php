<?php

namespace App\Integrations;

use App\Contracts\CreditScoreProvider;
use App\Exceptions\CreditBureauException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

final readonly class CreditBureauClient implements CreditScoreProvider
{
    public function scoreFor(string $cpf): int
    {
        try {
            $response = $this->http()->get($cpf);

            $response->throw();
        }
        catch (ConnectionException) {
            throw new CreditBureauException(
                'Não foi possível consultar o Bureau de Crédito.',
                504
            );
        }
        catch (RequestException $exception) {
            if ($exception->response->status() === 429) {
                throw new CreditBureauException(
                    'O Bureau de Crédito atingiu o limite de consultas. Tente novamente mais tarde.',
                    429
                );
            }

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

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('services.score_bureau.url'), '/'))
            ->timeout(config('services.score_bureau.timeout', 3))
            ->acceptJson();
    }
}
