<?php

namespace App\Enums;

enum PortionBasis: string
{
    case REFERENCE = 'reference';
    case USDA = 'usda';
    case MEASURED = 'measured';
    case RESTAURANT_MEASURED = 'restaurant_measured';
    case ESTIMATED = 'estimated';
}
