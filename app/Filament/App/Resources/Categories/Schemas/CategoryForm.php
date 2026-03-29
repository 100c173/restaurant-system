<?php

namespace App\Filament\App\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Restaurants\Models\Menu;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

       
            Section::make('General information')
                ->description('Basic details about this category.')
                ->icon('heroicon-o-tag')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('name')
                            ->label('Category name')
                            ->placeholder('e.g. Burgers, Sides, Drinks…')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('position')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first in the app.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                    Textarea::make('description')
                        ->label('Description')
                        ->placeholder('A short description shown to customers…')
                        ->rows(3)
                        ->maxLength(500)
                        ->nullable(),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Inactive categories are hidden from customers immediately.')
                        ->default(true)
                        ->inline(false),
                ]),

            
            Section::make('Menu assignment')
                ->description('Assign this category to a menu. Leave empty to make it an uncategorised global category.')
                ->icon('heroicon-o-book-open')
                ->schema([
                    Select::make('menu_id')
                        ->label('Menu')
                        ->placeholder('Select a menu…')
                        ->options(
                            fn() => Menu::active()
                                ->ordered()
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->helperText('Only active menus are listed.'),
                ]),

            
            Section::make('Category image')
                ->description('Upload an image shown next to this category in the mobile app.')
                ->icon('heroicon-o-photo')
                ->schema([
                    FileUpload::make('img_path')
                        ->label('Image')
                        ->image()
                        ->disk('tenant_uploads')
                        ->directory('categories')
                        ->imageEditor()
                        ->visibility('public')
                        ->maxSize(2048)
                        ->nullable(),
                ])
                ->collapsible(),

        ]);
    }
}
