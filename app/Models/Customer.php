<?php

namespace App\Models;

use App\Models\Concerns\HasHashidsCode;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory, HasHashidsCode;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf',
        'email',
        'telefone',
        'renda_mensal',
    ];

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'renda_mensal' => 'decimal:2',
        ];
    }

    /**
     * A customer may have many credit analyses.
     */
    public function creditAnalyses(): HasMany
    {
        return $this->hasMany(CreditAnalysis::class, 'cliente_id');
    }
}
