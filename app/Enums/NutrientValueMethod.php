<?php

namespace App\Enums;

enum NutrientValueMethod: string
{
    case IMPORTED = 'imported';
    case ESTIMATED = 'estimated';
    case MEASURED = 'measured';
    case LABORATORY = 'laboratory';
}
