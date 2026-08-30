<?php

namespace App\Models;

use App\Enums\AnalysisStatus;
use App\Enums\CreditType;
use App\Models\Concerns\HasHashidsCode;
use Database\Factories\CreditAnalysisFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property AnalysisStatus $status
 * @property CreditType $tipo_credito
 */
class CreditAnalysis extends Model
{
    use HasFactory, HasHashidsCode;

    protected $table = 'analises_credito';

    protected $fillable = [
        'cliente_id',
        'cpf',
        'nome',
        'renda_mensal',
        'tipo_credito',
        'valor_solicitado',
        'status',
        'score',
        'taxa_juros',
        'valor_parcela',
        'motivo_rejeicao',
    ];

    protected static function newFactory(): CreditAnalysisFactory
    {
        return CreditAnalysisFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => AnalysisStatus::class,
            'tipo_credito' => CreditType::class,
            'renda_mensal' => 'decimal:2',
            'valor_solicitado' => 'decimal:2',
            'taxa_juros' => 'decimal:2',
            'valor_parcela' => 'decimal:2',
            'score' => 'integer',
        ];
    }

    /**
     * A credit analysis belongs to a customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'cliente_id');
    }
}
