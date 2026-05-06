<?php

namespace App\Filament\Resources\Features;

use App\Filament\Resources\Features\Pages\ManageFeatures;
use App\Models\Feature;
use App\Models\Plan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FeatureResource extends Resource
{
    protected static ?string $model = Feature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feature details')
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label('Feature name')
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
                            ->helperText('Auto-generated. Used in application logic — do not change after seeding.')
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),

                        Select::make('type')
                            ->label('Type')
                            ->required()
                            ->options([
                                'boolean' => 'Boolean  — true / false',
                                'limit' => 'Limit    — numeric (−1 = unlimited)',
                                'text' => 'Text     — free string',
                            ])
                            ->default('boolean')
                            ->helperText('Determines how the value is interpreted at runtime.'),

                        Textarea::make('description')
                            ->label('Description (optional)')
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Feature')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn(Feature $record) => $record->description),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->fontFamily('mono'),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'boolean' => 'warning',
                        'limit' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                TextColumn::make('plans_count')
                    ->label('Plans')
                    ->counts('plans')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'boolean' => 'Boolean',
                        'limit' => 'Limit',
                        'text' => 'Text',
                    ]),
            ])

            ->actions([

                // ── Assign this feature to a plan ─────────────────────
                Action::make('assign_to_plan')
                    ->label('Assign to plan')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->modalHeading(fn(Feature $record) => "Assign to plan — {$record->name}")
                    ->modalDescription(
                        fn(Feature $record) =>
                        "Select a plan and set the value for \"{$record->name}\"."
                    )
                    ->form(function (Feature $record) {
                        // Plans that don't already have this feature
                        $assigned = $record->plans()->pluck('plans.id');

                        return [
                            Select::make('plan_id')
                                ->label('Plan')
                                ->required()
                                ->searchable()
                                ->options(
                                    fn() => Plan::query()
                                        ->pluck('name', 'id')
                                        ->toArray()
                                )
                                ->helperText('Search by name or code. Only plans without this feature are shown.'),

                            TextInput::make('value')
                                ->label('Value')
                                ->required()
                                ->helperText(
                                    match ($record->type) {
                                        'boolean' => 'Enter: true  or  false',
                                        'limit' => 'Enter a number, or -1 for unlimited.',
                                        'text' => 'Enter any display string.',
                                    }
                                ),
                        ];
                    })
                    ->action(function (Feature $record, array $data): void {
                        $record->plans()->attach($data['plan_id'], [
                            'value' => $data['value'],
                        ]);

                        Notification::make()
                            ->title('Feature assigned to plan')
                            ->success()
                            ->send();
                    }),

                // ── View plans using this feature ─────────────────────
                Action::make('view_plans')
                    ->label('View plans')
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('info')
                    ->url(fn(Feature $record) => FeatureResource::getUrl('view-plans', ['record' => $record])),

                EditAction::make()
                    ->modalHeading('Edit feature'),

                DeleteAction::make()
                    ->before(function (Feature $record, DeleteAction $action) {
                        if ($record->plans()->exists()) {
                            Notification::make()
                                ->title('Cannot delete')
                                ->body('This feature is assigned to one or more plans. Remove it from all plans first.')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])

            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('name', 'asc')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFeatures::route('/'),
            'create' => Pages\CreateFeature::route('/create'),
            'edit' => Pages\EditFeature::route('/{record}/edit'),
            'view-plans' => Pages\ViewFeaturePlans::route('/{record}/plans'),
        ];
    }
}
