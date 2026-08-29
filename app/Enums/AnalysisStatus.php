<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case PENDING = 'pendente';
    case APPROVED = 'aprovado';
    case REJECTED = 'reprovado';
    case PROCESSING_CONTRACT = 'processando_contratacao';
    case CONTRACTED = 'contratado';

    public function canBeViewedInSimulation(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::PROCESSING_CONTRACT,
            self::CONTRACTED,
        ], true);
    }
}
