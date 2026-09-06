<?php

namespace App\Enums;

enum FoodSourceType: string
{
    case USDA_FDC = 'usda_fdc';
    case LOCAL_REFERENCE = 'local_reference';
    case RESTAURANT_MEASUREMENT = 'restaurant_measurement';
    case LAB = 'lab';
}