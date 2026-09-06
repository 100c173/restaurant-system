<?php

namespace App\Enums;

enum ConfidenceLevel: string
{
    case CALCULATED = 'calculated';
    case REFERENCE = 'reference';
    case LOCAL_REFERENCE = 'local_reference';
    case MEASURED = 'measured';
    case REVIEWED = 'reviewed';
    case VERIFIED = 'verified';
}

