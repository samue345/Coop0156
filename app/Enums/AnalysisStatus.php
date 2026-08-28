<?php

namespace App\Enums;

enum AnalysisStatus: string
{
    case PENDING = 'pendente';
    case APPROVED = 'aprovado';
    case REJECTED = 'reprovado';
    case PROCESSING_CONTRACT = 'processando_contratacao';
    case CONTRACTED = 'contratado';
}
