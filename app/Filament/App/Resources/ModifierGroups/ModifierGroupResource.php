<?php

namespace App\Filament\App\Resources\ModifierGroups;

use App\Filament\App\Resources\ModifierGroups\Pages\ManageModifierGroups;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\Restaurants\Models\ModifierGroup;
use UnitEnum;

class ModifierGroupResource extends Resource
{
    protected static ?string $model = ModifierGroup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Modifier Group';
    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Upper bound on selections to prevent absurd database values.
     * Raise if a legitimate use case requires more.
     */
    private const MAX_ALLOWED_SELECTIONS = 99;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Group Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Choose your sauce'),

                        Toggle::make('is_required')
                            ->label('Required Selection')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, bool $state) {
                                // A required group must demand at least 1 selection.
                                // If the operator marks it required but min is still 0,
                                // bump min to 1 silently so the constraint is satisfied.
                                if ($state && (int) $get('min_selections') === 0) {
                                    $set('min_selections', 1);
                                }
                            }),

                        Toggle::make('is_multiple')
                            ->label('Allow Multiple Selections')
                            ->default(false)
                            ->reactive()
                            ->afterStateUpdated(function (Set $set, Get $get, bool $state) {
                                if (! $state) {
                                    // Single-select: exactly one choice.
                                    // Clamp min to 0 or 1 (keep whatever was set if valid),
                                    // hard-set max to 1.
                                    $min = (int) $get('min_selections');
                                    $set('min_selections', min($min, 1));
                                    $set('max_selections', 1);
                                }
                                // When switching back to multiple, release the cap;
                                // operator sets their own max. No silent override needed.
                            }),
                    ])
                    ->columns(2),

                Section::make('Selection Rules')
                    ->schema([
                        TextInput::make('min_selections')
                            ->label('Minimum Selections')
                            ->numeric()
                            ->integer()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(self::MAX_ALLOWED_SELECTIONS)
                            ->reactive()
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $value = (int) $value;
                                        $max   = $get('max_selections');
                                        $isRequired  = (bool) $get('is_required');
                                        $isMultiple  = (bool) $get('is_multiple');

                                        // A "required" group with min=0 is logically contradictory.
                                        if ($isRequired && $value === 0) {
                                            $fail('A required group must have a minimum of at least 1 selection.');
                                        }

                                        // Single-select groups cannot require more than 1.
                                        if (! $isMultiple && $value > 1) {
                                            $fail('Minimum selections cannot exceed 1 when multiple selections are disabled.');
                                        }

                                        // Cross-field: min must not exceed max (when max is set).
                                        if (! is_null($max) && $value > (int) $max) {
                                            $fail('Minimum selections cannot exceed maximum selections.');
                                        }

                                        // Sanity cap — matches the TextInput::maxValue() above.
                                        if ($value > self::MAX_ALLOWED_SELECTIONS) {
                                            $fail('Minimum selections cannot exceed ' . self::MAX_ALLOWED_SELECTIONS . '.');
                                        }
                                    };
                                },
                            ]),

                        TextInput::make('max_selections')
                            ->label('Maximum Selections')
                            ->numeric()
                            ->integer()
                            ->nullable()
                            ->minValue(1)
                            ->maxValue(self::MAX_ALLOWED_SELECTIONS)
                            ->placeholder('Leave blank for unlimited')
                            ->reactive()
                            ->rules([
                                function (Get $get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        // null means "unlimited" — skip further checks.
                                        if (is_null($value) || $value === '') {
                                            return;
                                        }

                                        $value = (int) $value;
                                        $min   = (int) $get('min_selections');
                                        $isMultiple = (bool) $get('is_multiple');

                                        // max must be ≥ 1 (already enforced by minValue, but
                                        // explicit rule gives a clear message over a generic one).
                                        if ($value < 1) {
                                            $fail('Maximum selections must be at least 1.');
                                        }

                                        // Cross-field: max must not be less than min.
                                        if ($value < $min) {
                                            $fail('Maximum selections must be greater than or equal to minimum selections.');
                                        }

                                        // Single-select groups are capped at 1.
                                        if (! $isMultiple && $value > 1) {
                                            $fail('Maximum selections cannot exceed 1 when multiple selections are disabled.');
                                        }

                                        // Sanity cap.
                                        if ($value > self::MAX_ALLOWED_SELECTIONS) {
                                            $fail('Maximum selections cannot exceed ' . self::MAX_ALLOWED_SELECTIONS . '.');
                                        }
                                    };
                                },
                            ]),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_required')
                    ->boolean()
                    ->label('Required'),

                IconColumn::make('is_multiple')
                    ->boolean()
                    ->label('Multiple'),

                TextColumn::make('min_selections')
                    ->label('Min'),

                TextColumn::make('max_selections')
                    ->label('Max')
                    ->formatStateUsing(fn ($state) => $state ?? '∞'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_required'),
                TernaryFilter::make('is_multiple'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageModifierGroups::route('/'),
        ];
    }
}