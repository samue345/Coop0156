<?php

namespace App\Enums;

enum CreditType: string
{
    case PERSONAL = 'pessoal';
    case REAL_ESTATE = 'imobiliario';
    case VEHICLE = 'automotivo';
}
