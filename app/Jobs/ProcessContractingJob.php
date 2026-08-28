<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * ⭐ DIFERENCIAL OPCIONAL — ProcessContractingJob
 *
 * Este Job é um diferencial do desafio. Sua implementação NÃO é obrigatória,
 * mas demonstra conhecimento em processamento assíncrono com Laravel Queues.
 *
 * Para utilizá-lo:
 *  - No método `contratar` do CreditAnalysisController, em vez de atualizar o
 *    status diretamente para 'contratado', atualize para 'processando_contratacao'
 *    e dispare este Job: ProcessContractingJob::dispatch($analysisId);
 *  - Configure a fila no .env: QUEUE_CONNECTION=database
 *  - Execute o worker: php artisan queue:work
 */
class ProcessContractingJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $analysisId)
    {
        //
    }

    /**
     * Execute the job.
     *
     * TODO (Diferencial): Buscar a CreditAnalysis pelo $analysisId,
     * atualizar o status para 'contratado' e registrar um log de sucesso.
     */
    public function handle(): void
    {
        //
    }
}
