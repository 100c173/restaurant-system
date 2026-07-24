<?php
namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use App\Models\Food;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
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
            ->with('food'); // one extra query for all foods on this page, not one per row
    }

    private function formSchema(): array
    {
        return [
            Select::make('food_id')
                ->label('Ingredient')
                ->searchable()
                ->allowHtml()
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
                ]),

            TextInput::make('quantity_grams')
                ->label('Quantity')
                ->numeric()
                ->required()
                ->minValue(1)
                ->default(100)
                ->suffix('g'),

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

            TextColumn::make('quantity_grams')
                ->label('Quantity')
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
}