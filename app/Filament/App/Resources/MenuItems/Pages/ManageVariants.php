<?php

namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemVariant;

class ManageVariants extends Page implements HasTable
{
    use InteractsWithTable;

    public MenuItem $record;
    protected static string $resource = MenuItemResource::class;
    protected string $view = 'restaurants::filament.app.resources.menu-items.pages.manage-variants';

    public function mount(MenuItem $record): void
    {
        $this->record = $record;
    }

    protected function getTableQuery(): Builder
    {
        return MenuItemVariant::query()
            ->where('menu_item_id', $this->record->id);
    }
    protected function getTableFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('price')
                ->numeric()
                ->minValue(0)
                ->required(),

            Toggle::make('is_available')
                ->default(true),
        ];
    }
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('name'),
            TextColumn::make('price')
                ->label('add to price'),

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
                ->label("add")
                ->form($this->getTableFormSchema())
                ->modalHeading("{$this->record->name}")
                ->mutateFormDataUsing(function (array $data) {
                    $data['menu_item_id'] = $this->record->id;
                    return $data;
                }),
        ];
    }
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit')
                ->modalHeading(fn($record) => "Edit Variant ({$record->name}) for {$this->record->name}")
                ->form($this->getTableFormSchema()),
            DeleteAction::make(),
        ];
    }
    public function getTitle(): string
    {
        return "{$this->record->name} - Manage Variants";
    }
}
