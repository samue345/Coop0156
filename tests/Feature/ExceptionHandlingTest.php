<?php

namespace Tests\Feature;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PDOException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ExceptionHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_hides_database_details_from_api_query_exception_responses(): void
    {
        Route::get('/api/test-query-exception', function (): never {
            throw new QueryException(
                'sqlite',
                'select * from clientes where email = secret@example.com',
                [],
                new PDOException('NOT NULL constraint failed: clientes.email'),
            );
        });

        $this->getJson('/api/test-query-exception')
            ->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
            ->assertJson([
                'message' => 'Não foi possível processar a solicitação. Tente novamente mais tarde.',
            ])
            ->assertJsonMissing(['sql' => 'select * from clientes where email = secret@example.com'])
            ->assertJsonMissing(['exception' => QueryException::class]);
    }
}
