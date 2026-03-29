<?php

namespace App\Filament\App\Resources\Menus\RelationManagers;

use App\Filament\App\Resources\Categories\Schemas\CategoryForm;
use App\Filament\App\Resources\Categories\Tables\CategoriesTable;
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
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'categories';

    protected static ?string $title = 'Categories in this menu';

    public function form(Schema $schema): Schema
    {
        return CategoryForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CategoriesTable::configure($table)
            ->heading('Categories')
            ->description('Manage the categories that belong to this menu.')
            ->headerActions([
                CreateAction::make()
                    ->label('Add category')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add category to this menu')
                    // Pre-fill menu_id with the current menu's ID
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['menu_id'] = $this->getOwnerRecord()->getKey();
                        return $data;
                    }),
            ]);
    }
}
