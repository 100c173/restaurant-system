<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecipeStatus: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Published => 'منشورة',
            self::Archived => 'مؤرشفة',
        };
    }
}
