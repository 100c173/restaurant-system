<?php

namespace App\Filament\App\Resources\MenuItemAnalyses\Pages;

use App\Filament\App\Resources\MenuItemAnalyses\MenuItemAnalysisResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Modules\Restaurants\Models\MenuItemAnalysis;

class ViewMenuItemAnalysis extends ViewRecord
{
    protected static string $resource = MenuItemAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->getRecord())
            ->components([
                Section::make(fn (MenuItemAnalysis $record) => $record->menuItem?->name ?? 'Meal analysis')
                    ->description(fn (MenuItemAnalysis $record) => "Total weight: {$record->total_grams} g")
                    ->components([
                        Grid::make(4)
                            ->components([
                                TextEntry::make('energy_kcal')
                                    ->label('Energy')
                                    ->suffix(' kcal')
                                    ->placeholder('—')
                                    ->weight('bold')
                                    ->size('lg'),
                                TextEntry::make('protein_g')->label('Protein')->suffix(' g')->placeholder('—'),
                                TextEntry::make('carbs_g')->label('Carbs')->suffix(' g')->placeholder('—'),
                                TextEntry::make('fat_total_g')->label('Fat')->suffix(' g')->placeholder('—'),
                            ]),
                    ]),

                Section::make('Macronutrient detail')
                    ->columns(3)
                    ->components([
                        TextEntry::make('fiber_g')->label('Fiber')->suffix(' g')->placeholder('—'),
                        TextEntry::make('sugars_total_g')->label('Sugars')->suffix(' g')->placeholder('—'),
                    ]),

                Section::make('Micronutrients')
                    ->columns(3)
                    ->components([
                        TextEntry::make('calcium_mg')->label('Calcium')->suffix(' mg')->placeholder('—'),
                        TextEntry::make('iron_mg')->label('Iron')->suffix(' mg')->placeholder('—'),
                        TextEntry::make('sodium_mg')->label('Sodium')->suffix(' mg')->placeholder('—'),
                        TextEntry::make('potassium_mg')->label('Potassium')->suffix(' mg')->placeholder('—'),
                        TextEntry::make('vitamin_c_mg')->label('Vitamin C')->suffix(' mg')->placeholder('—'),
                        TextEntry::make('vitamin_a_rae_ug')->label('Vitamin A')->suffix(' µg')->placeholder('—'),
                    ]),

                Section::make('Data quality')
                    ->icon(fn (MenuItemAnalysis $record) => blank($record->warnings)
                        ? Heroicon::OutlinedCheckCircle
                        : Heroicon::OutlinedExclamationTriangle)
                    ->iconColor(fn (MenuItemAnalysis $record) => blank($record->warnings) ? 'success' : 'warning')
                    ->description(fn (MenuItemAnalysis $record) => blank($record->warnings)
                        ? 'Every ingredient reported every nutrient.'
                        : 'Some totals below are undercounted because these ingredients were missing data.')
                    ->components([
                        RepeatableEntry::make('warnings_list')
                            ->label('')
                            ->state(fn (MenuItemAnalysis $record): array => collect($record->warnings ?? [])
                                ->map(fn ($foods, $nutrient) => [
                                    'nutrient' => $nutrient,
                                    'foods' => implode(', ', $foods),
                                ])
                                ->values()
                                ->toArray())
                            ->components([
                                TextEntry::make('nutrient')->label('Nutrient')->badge(),
                                TextEntry::make('foods')->label('Missing for'),
                            ])
                            ->columns(2)
                            ->visible(fn (MenuItemAnalysis $record) => filled($record->warnings)),

                        TextEntry::make('none')
                            ->label('')
                            ->state('No missing data.')
                            ->visible(fn (MenuItemAnalysis $record) => blank($record->warnings)),
                    ]),

                TextEntry::make('updated_at')
                    ->label('Last analyzed')
                    ->dateTime(),
            ]);
    }
}