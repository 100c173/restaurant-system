<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ImportBatchStatus: string implements HasLabel
{
    case Uploaded = 'uploaded';
    case Validated = 'validated';
    case Committed = 'committed';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return match ($this) {
            self::Uploaded => 'مرفوعة',
            self::Validated => 'مفحوصة',
            self::Committed => 'محفوظة (قيد المراجعة)',
            self::Failed => 'فشلت',
        };
    }
}
