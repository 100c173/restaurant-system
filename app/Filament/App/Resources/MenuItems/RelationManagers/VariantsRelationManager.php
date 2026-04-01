<?php

namespace App\Filament\App\Resources\MenuItems\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(1)->schema([

                    Section::make('Variant Details')
                        ->description('Configure the options for this menu item variant')
                        ->icon('heroicon-o-cube')
                        ->collapsible()
                        ->schema([
                            TextInput::make('name')
                                ->label('Variant name')
                                ->placeholder('Small, Medium, Large, Extra Spicy...')
                                ->required()
                                ->maxLength(255)
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'w-full text-lg py-2']),

                            TextInput::make('price')
                                ->label('Price')
                                ->placeholder('0.00')
                                ->required()
                                ->numeric()
                                ->prefix('$')
                                ->step(0.01)
                                ->minValue(0)
                                ->helperText('This price overrides the base menu item price')
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'w-full text-xl py-3 font-semibold']),

                            TextInput::make('position')
                                ->label('Display position')
                                ->placeholder('0')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->helperText('Lower numbers appear first in the list')
                                ->columnSpanFull()
                                ->extraAttributes(['class' => 'w-full text-lg py-2']),

                            Toggle::make('is_available')
                                ->label('Available for ordering')
                                ->helperText('When disabled, this variant will not be shown to customers')
                                ->default(true)
                                ->inline(false)
                                ->columnSpanFull(),
                        ]),

                ])->columnSpanFull(),
            ]);
    }
    public function table(Table $table): Table
    {
        return $table
            ->heading('Size & portion variants')
            ->description(
                'Variants adjust the base price.'
            )
            ->columns([

                // Position badge
                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->width('56px'),

                // Variant name
                TextColumn::make('name')
                    ->label('Variant')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                // Variant adjustment (shows how much it adds or subtracts)
                TextColumn::make('price')
                    ->label('Price adjustment')
                    ->formatStateUsing(function ($state): string {
                        if ($state > 0) {
                            return '+ ' . number_format($state, 2) . ' SP';
                        } elseif ($state < 0) {
                            return '- ' . number_format(abs($state), 2) . ' SP';
                        }
                        return '0 SP';
                    })
                    ->sortable()
                    ->color(function ($record): string {
                        $adjustment = $record->price;
                        if ($adjustment > 0)
                            return 'danger';    // Red for increase
                        if ($adjustment < 0)
                            return 'success';  // Green for discount
                        return 'gray';
                    })
                    ->weight('medium'),

                // Final price calculation (base price + adjustment)
                TextColumn::make('final_price')
                    ->label('Final price')
                    ->state(function ($record): string {
                        $base = $record->menuItem?->price ?? 0;
                        $adjustment = $record->price ?? 0;
                        $final = $base + $adjustment;

                        return number_format($final, 2) . ' SP';
                    })
                    ->sortable()
                    ->weight('semibold')
                    ->color('primary'),

                // Availability
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn(bool $state): string => $state ? 'Available' : 'Unavailable'),

                // Timestamps (hidden by default)
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('position', 'asc')

            ->headerActions([
                CreateAction::make()
                    ->label('Add variant')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add variant')
                    ->modalDescription(
                        fn() => 'Adding a variant to: ' . $this->getOwnerRecord()->name
                    )
                    ->modalWidth('xl'),
            ])

            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit variant')
                    ->modalHeading('Edit variant')
                    ->modalWidth('xl'),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete variant')
                    ->requiresConfirmation()
                    ->modalHeading('Delete variant')
                    ->modalDescription('Are you sure you want to delete this variant?')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])

            ->emptyStateIcon('heroicon-o-squares-2x2')
            ->emptyStateHeading('No variants yet')
            ->emptyStateDescription(
                'Add a variant (e.g. Small, Medium, Large) with a price adjustment. Positive values increase the price, negative values give a discount.'
            )
            ->striped();
    }
}
