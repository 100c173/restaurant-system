<?php

namespace App\Filament\App\Resources\Categories\Schemas;

use App\Services\CloudinaryUploadService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Restaurants\Models\Menu;

class CategoriesForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make('Category Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        Select::make('menu_id')
                            ->label('Menu')
                            ->relationship('menu', 'name')
                            ->options(Menu::ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('— No menu assigned —'),

                        TextInput::make('position')
                            ->label('Display Position')
                            ->numeric()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Category Image')
                    ->schema([
                        FileUpload::make('img_path')
                            ->label('Image')
                            ->image()
                            ->saveUploadedFileUsing(function ($file, $record) {
                                $uploader = new CloudinaryUploadService();

                                // Delete old image if exists
                                if ($record?->img_path) {
                                    $uploader->delete($record->img_path);
                                }
                                return $uploader->upload($file->getRealPath(), 'categories');
                            }),
                    ]),
            ]);
    }
}
