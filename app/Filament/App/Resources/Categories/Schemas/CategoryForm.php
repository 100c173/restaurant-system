<?php

namespace App\Filament\App\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                TextInput::make('description'),
                TextInput::make('position'),
                FileUpload::make('img_path')
                    ->image()
                    ->disk('tenant_uploads')
                    ->directory('categories/logo')
                    ->visibility('public')
                    ->maxSize(1024)
                    ->nullable(),
            ]);
    }
}
