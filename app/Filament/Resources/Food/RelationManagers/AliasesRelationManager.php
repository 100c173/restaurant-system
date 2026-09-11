<?php

namespace App\Filament\Resources\Food\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class AliasesRelationManager extends RelationManager
{
    protected static string $relationship = 'aliases';

    protected static ?string $title = 'الأسماء البديلة';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name_ar')
                ->label('الاسم بالعربي')
                ->required()
                ->live(onBlur: true)
                ->afterStateUpdated(fn ($state, callable $set) => $set('search_normalized', (string) Str::of($state)->squish()->lower()))
                ->maxLength(255),

            TextInput::make('name_en')
                ->label('الاسم بالإنكليزي')
                ->maxLength(255),

            TextInput::make('search_normalized')
                ->label('مفتاح البحث')
                ->required()
                ->maxLength(255),

            TextInput::make('dialect')
                ->label('اللهجة')
                ->maxLength(32),

            TextInput::make('region_code')
                ->label('رمز المنطقة')
                ->maxLength(12),

            Toggle::make('is_preferred')
                ->label('الاسم المفضل؟'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_ar')
            ->columns([
                TextColumn::make('name_ar')->label('الاسم بالعربي')->searchable(),
                TextColumn::make('name_en')->label('الاسم بالإنكليزي'),
                TextColumn::make('dialect')->label('اللهجة'),
                IconColumn::make('is_preferred')->label('مفضل')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
