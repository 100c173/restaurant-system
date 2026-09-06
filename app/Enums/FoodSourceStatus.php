<?php

namespace App\Enums;

enum FoodSourceStatus: string
{
    case ACTIVE = 'active';
    case SUPERSEDED = 'superseded';
    case REJECTED = 'rejected';
}
