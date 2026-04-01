<?php

namespace App\Filament\App\Resources\Menus\RelationManagers;

use App\Filament\App\Resources\Categories\CategoryResource;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
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
        return $table
            ->heading('Categories')
            ->description('Manage the categories in this menu. Click a row to view its details and items.')
            ->columns([

                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->width('56px'),

                ImageColumn::make('img_path')
                    ->label('Image')
                    ->circular()
                    ->defaultImageUrl(asset('images/category-placeholder.png'))
                    ->width(40),

                TextColumn::make('name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->description(
                        fn($record): ?string => $record->description
                        ? str($record->description)->limit(55)
                        : null
                    ),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn(bool $state): string => $state ? 'Active' : 'Inactive'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('position', 'asc')

            // ── Clicking a category row navigates to its own view page ────
            ->recordUrl(
                fn($record): string => CategoryResource::getUrl('view', ['record' => $record])
            )

            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All categories')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])

            ->headerActions([
                // Add category button → modal
                CreateAction::make()
                    ->label('Add category')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Add category')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Create category')
                    // Pre-fill menu_id with the current menu
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['menu_id'] = $this->getOwnerRecord()->getKey();
                        return $data;
                    }),
            ])

            ->actions([
                // Edit → modal (stays on this page)
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit category')
                    ->modalHeading('Edit category')
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Save changes'),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete category')
                    ->requiresConfirmation()
                    ->modalHeading('Delete category')
                    ->modalDescription('Are you sure? Items inside this category may be affected.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])

            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Add your first category to start organising your menu items.')
            ->striped();
    }
}
