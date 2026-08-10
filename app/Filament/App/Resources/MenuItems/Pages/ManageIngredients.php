<?php

namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use App\Models\Food;
use App\Models\FoodPortion;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemIngredient;
use Modules\Restaurants\Services\MenuItemIngredientService;

class ManageIngredients extends Page implements HasTable
{
    use InteractsWithTable;

    public MenuItem $record;

    protected static string $resource = MenuItemResource::class;
    protected string $view = 'restaurants::filament.app.resources.menu-items.pages.manage-ingredients';

    public function mount(MenuItem $record): void
    {
        $this->record = $record;
    }

    protected function getTableQuery(): Builder
    {
        return MenuItemIngredient::query()
            ->where('menu_item_id', $this->record->id)
            ->with(['food', 'portion.measureUnit']); // avoid N+1 on the display column
    }

    private function formSchema(): array
    {
        return [
            Select::make('food_id')
                ->label('Ingredient')
                ->searchable()
                ->allowHtml()
                ->live()
                ->getSearchResultsUsing(
                    fn(string $search) => Food::query()
                        ->where('name_ar', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->limit(50)
                        ->get()
                        ->mapWithKeys(fn($food) => [$food->id => self::foodOptionLabel($food)])
                        ->toArray()
                )
                ->getOptionLabelUsing(fn($value) => Food::find($value)?->name_ar)
                ->required()
                ->unique(
                    table: 'menu_item_ingredients',
                    column: 'food_id',
                    modifyRuleUsing: fn($rule) => $rule->where('menu_item_id', $this->record->id),
                    ignoreRecord: true,
                )
                ->validationMessages([
                    'unique' => 'This ingredient is already added to this item.',
                ])
                // Changing the ingredient invalidates whatever unit/portion was picked before
                ->afterStateUpdated(fn(Set $set) => $set('portion_id', null)),

            Select::make('portion_id')
                ->label('Unit')
                ->live()
                ->required()
                ->native(false)
                ->allowHtml()
                ->disabled(fn(Get $get): bool => blank($get('food_id')))
                ->options(fn(Get $get): array => self::portionOptionsFor($get('food_id')))
                ->helperText('اختر طريقة قياس هذا المكون. الغرامات الموضحة هي وزن وحدة واحدة من هذا المكون لهذا الطعام المحدد')
                ->hintIcon(Heroicon::OutlinedInformationCircle)
                ->hintIconTooltip('تأتي الأوزان من بيانات مرجعية لوزارة الزراعة الأمريكية وتختلف باختلاف الطعام - فـ "كوب واحد" من الأرز و "كوب واحد" من السبانخ ليسا بنفس الوزن بالجرام'),

            TextInput::make('quantity')
                ->label('Quantity')
                ->numeric()
                ->required()
                ->minValue(0.001)
                ->step(0.001)
                ->default(1)
                ->live(onBlur: true),

            TextEntry::make('quantity_grams_preview')
                ->label('Total weight')
                ->live()
                ->state(fn(Get $get): string => self::previewGrams($get) . ' g'),

            Textarea::make('notes')
                ->label('Notes')
                ->rows(2)
                ->placeholder('e.g. finely chopped')
                ->nullable(),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('food.name_ar')
                ->label('Ingredient')
                ->description(fn($record) => $record->food?->description),

            TextColumn::make('portion_display')
                ->label('Amount')
                ->state(function (MenuItemIngredient $record): string {
                    if (!$record->portion) {
                        return "{$record->quantity}";
                    }

                    return "{$record->quantity} × " . self::portionLabel($record->portion);
                }),

            TextColumn::make('quantity_grams')
                ->label('Total weight')
                ->suffix(' g'),

            TextColumn::make('notes')
                ->label('Notes')
                ->limit(40)
                ->placeholder('—'),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            Action::make('analyze')
                ->label('Analyze Meal')
                ->icon('heroicon-o-calculator')
                ->color('primary')
                ->disabled(fn(): bool => !MenuItemIngredient::where('menu_item_id', $this->record->id)->exists())
                ->tooltip(fn(): ?string => MenuItemIngredient::where('menu_item_id', $this->record->id)->exists()
                    ? null
                    : 'Add at least one ingredient before analyzing.')
                ->authorize(fn (): bool => MenuItemResource::canAnalysis())

                ->action(function (): void {
                    $analysis = app(MenuItemIngredientService::class)->analyze($this->record);

                    Notification::make()
                        ->title('Meal analyzed')
                        ->body(
                            "Total weight: {$analysis->total_grams} g"
                            . ($analysis->energy_kcal !== null ? " — {$analysis->energy_kcal} kcal" : ' — no energy data available')
                        )
                        ->success()
                        ->send();
                }),

            CreateAction::make()
                ->label('Add Ingredient')
                ->modalHeading('Add Ingredient')
                ->form($this->formSchema())
                ->using(function (array $data): MenuItemIngredient {
                    return app(MenuItemIngredientService::class)->add($this->record, $data);
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Edit Ingredient')
                ->form($this->formSchema())
                ->using(function (MenuItemIngredient $record, array $data): MenuItemIngredient {
                    return app(MenuItemIngredientService::class)->update($record, $data);
                }),

            DeleteAction::make()
                ->using(function (MenuItemIngredient $record): void {
                    app(MenuItemIngredientService::class)->remove($record);
                }),
        ];
    }

    public function getTitle(): string
    {
        return "{$this->record->name} - Ingredients";
    }

    private static function foodOptionLabel(Food $food): string
    {
        $name = e($food->name_ar);
        $description = $food->description
            ? '<br><span class="text-xs text-gray-500">' . e($food->description) . '</span>'
            : '';

        return $name . $description;
    }

    /**
     * All food_portions rows for the selected food, formatted per spec:
     * "{amount} {unit}{ (modifier)} ≈ {gram_weight}g"
     *
     * @return array<int, string>
     */
    private static function portionOptionsFor(?int $foodId): array
    {
        if (blank($foodId)) {
            return [];
        }

        return FoodPortion::query()
            ->where('food_id', $foodId)
            ->with('measureUnit')
            ->get()
            ->mapWithKeys(fn(FoodPortion $portion) => [$portion->id => self::portionOptionLabel($portion)])
            ->toArray();
    }
    /**
     * Rich two-line label for the Select dropdown: bold "amount unit (modifier)"
     * on top, muted "≈ Xg" underneath -- so users read the unit first and the
     * weight as supporting context, not as one run-on string.
     */
    private static function portionOptionLabel(FoodPortion $portion): string
    {
        $amount = self::trimNumber((float) $portion->amount, 3);
        $unit = $portion->measureUnit?->name_ar ?? 'unit';
        $modifier = $portion->modifier ? " ({$portion->modifier})" : '';
        $gramWeight = self::trimNumber((float) $portion->gram_weight, 2);

        //if($unit == 'undetermined')$unit='';

        $primary = e("{$amount} {$unit}{$modifier}");
        $secondary = e("≈ {$gramWeight}g");


        return <<<HTML
        <div class="leading-tight">
            <div class="font-medium">{$primary}</div>
            <div class="text-xs text-gray-500">{$secondary}</div>
        </div>
        HTML;
    }
    private static function portionLabel(FoodPortion $portion): string
    {
        $amount = self::trimNumber((float) $portion->amount, 3);
        $unit = $portion->measureUnit?->name_ar ?? 'unit';
        $modifier = $portion->modifier ? " ({$portion->modifier})" : '';
        $gramWeight = self::trimNumber((float) $portion->gram_weight, 2);

        //if($unit == 'undetermined')$unit='';

        return "{$amount} {$unit}{$modifier} ≈ {$gramWeight}g";
    }

    /**
     * Live preview shown under the quantity field: quantity × selected portion's gram_weight.
     */
    private static function previewGrams(Get $get): string
    {
        $portionId = $get('portion_id');
        $quantity = (float) ($get('quantity') ?? 0);

        if (blank($portionId) || $quantity <= 0) {
            return '0';
        }

        $portion = FoodPortion::find($portionId);

        if (!$portion) {
            return '0';
        }

        return self::trimNumber($quantity * (float) $portion->gram_weight, 2);
    }

    private static function trimNumber(float $value, int $decimals): string
    {
        return rtrim(rtrim(number_format($value, $decimals, '.', ''), '0'), '.') ?: '0';
    }
}