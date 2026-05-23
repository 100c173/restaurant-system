<?php

namespace App\Filament\App\Resources\MenuItems\Pages;


use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemModifier;
use Modules\Restaurants\Models\Modifier;
use Modules\Restaurants\Models\ModifierGroup;

class ManageModifiers extends Page implements HasTable
{
    use InteractsWithTable;

    public MenuItem $record;

    protected static string $resource = MenuItemResource::class;
    protected string $view = 'restaurants::filament.app.resources.menu-items.pages.manage-modifiers';

    public function mount(MenuItem $record): void
    {
        $this->record = $record;
    }

    protected function getTableQuery(): Builder
    {
        return MenuItemModifier::query()
            ->where('menu_item_id', $this->record->id);
    }

    private function formSchema(): array
    {
        return [
            Select::make('modifier_group_id')
                ->label('Modifier Group')
                ->options(fn() => ModifierGroup::pluck('name', 'id')->toArray())
                ->searchable()
                ->required(),

            Select::make('modifier_id')
                ->label('Modifier')
                ->options(fn() => Modifier::pluck('name', 'id')->toArray())
                ->searchable()
                ->required(),

            TextInput::make('price_override')
                ->label('Price Override')
                ->numeric()
                ->minValue(0)
                ->nullable()
                ->placeholder('Leave empty to use modifier default'),

            Toggle::make('is_available')
                ->label('Available')
                ->default(true),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('modifierGroup.name')
                ->label('Modifier Group')
                ->sortable()
                ->searchable(),

            TextColumn::make('modifier.name')
                ->label('Modifier')
                ->sortable()
                ->searchable(),

            TextColumn::make('price_override')
                ->label('Price Override')
                ->money('SYP')
                ->placeholder('—'),

            IconColumn::make('is_available')
                ->label('Available')
                ->boolean()
                ->trueIcon('heroicon-o-check-circle')
                ->falseIcon('heroicon-o-x-circle')
                ->trueColor('success')
                ->falseColor('danger')
                ->alignCenter(),
        ];
    }

    protected function getTableHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add')
                ->modalHeading('Add Modifier')
                ->form($this->formSchema())
                ->mutateFormDataUsing(function (array $data): array {
                    $data['menu_item_id'] = $this->record->id;
                    return $data;
                }),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Edit Modifier')
                ->form($this->formSchema()),

            DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return "{$this->record->name} - Modifiers";
    }
}