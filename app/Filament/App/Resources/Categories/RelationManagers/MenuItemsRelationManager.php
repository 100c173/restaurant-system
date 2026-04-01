<?php

namespace App\Filament\App\Resources\Categories\RelationManagers;

use App\Filament\App\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\App\Resources\MenuItems\Tables\MenuItemsTable;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;

use Filament\Tables\Table;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuItems';

    public function form(Schema $schema): Schema
    {
        return MenuItemForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return MenuItemsTable::configure($table)
            ->heading('Menu items')
            ->description('Manage the items that belong to this category.')
            ->headerActions([
                CreateAction::make()
                    ->label('Add item')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add item to this category')
                    ->modalWidth('5xl')
                    // Pre-fill category_id so the owner never has to select it manually
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['category_id'] = $this->getOwnerRecord()->getKey();
                        return $data;
                    }),
            ]);
    }
}
