<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Resources\Plans\Pages\ManagePlans;
use App\Models\Feature;
use App\Models\Plan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = 'Subscriptions';


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plan details')
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label('Plan name')
                            ->required()
                            ->maxLength(100)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, Set $set) =>
                                $set('code', strtoupper(str($state)->slug('_')))
                            ),

                        TextInput::make('code')
                            ->label('Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(60)
                            ->helperText('Auto-generated. Used internally — do not change after seeding.')
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                        TextInput::make('price')
                            ->label('Price (USD)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefix('$')
                            ->default(0),

                        Select::make('billing_interval')
                            ->label('Billing interval')
                            ->required()
                            ->options([
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                                'lifetime' => 'Lifetime',
                            ])
                            ->default('monthly'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('billing_interval')
                    ->label('Interval')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'monthly' => 'info',
                        'yearly' => 'success',
                        'lifetime' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                TextColumn::make('features_count')
                    ->label('Features')
                    ->counts('features')
                    ->badge()
                    ->color('primary'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),

                SelectFilter::make('billing_interval')
                    ->label('Billing interval')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                        'lifetime' => 'Lifetime',
                    ]),
            ])

            ->actions([

                // ── View associated features ──────────────────────────
                Action::make('view_features')
                    ->label('View features')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->url(fn(Plan $record) => PlanResource::getUrl('view-features', ['record' => $record])),

                // ── Assign feature to this plan ───────────────────────
                Action::make('assign_feature')
                    ->label('Assign feature')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading(fn(Plan $record) => "Assign feature — {$record->name}")
                    ->modalDescription('Select a feature and set its value for this plan.')
                    ->form(function (Plan $record) {
                        // Features not yet assigned to this plan
                        $assigned = $record->features()->pluck('features.id');

                        return [
                            Select::make('feature_id')
                                ->label('Feature')
                                ->required()
                                ->searchable()
                                ->getSearchResultsUsing(function (string $search) use ($record) {
                                    $assigned = $record->features()->pluck('features.id');

                                    return Feature::whereNotIn('id', $assigned)
                                        ->where(
                                            fn($q) => $q
                                                ->where('name', 'like', "%{$search}%")
                                                ->orWhere('code', 'like', "%{$search}%")
                                        )
                                        ->orderBy('name')
                                        ->limit(20)
                                        ->pluck('name', 'id');
                                })
                                ->getOptionLabelUsing(fn($value) => Feature::find($value)?->name)
                                ->helperText('Search by name or code. Only unassigned features are shown.'),

                            TextInput::make('value')
                                ->label('Value')
                                ->required()
                                ->helperText(
                                    'Boolean features: true / false. ' .
                                    'Limit features: a number, or -1 for unlimited. ' .
                                    'Text features: any string.'
                                ),
                        ];
                    })
                    ->action(function (Plan $record, array $data): void {
                        $record->features()->attach($data['feature_id'], [
                            'value' => $data['value'],
                        ]);

                        Notification::make()
                            ->title('Feature assigned')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->modalHeading('Edit plan'),

                DeleteAction::make(),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('price', 'asc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
            'view-features' => Pages\ViewPlanFeatures::route('/{record}/features'),
        ];
    }
}
