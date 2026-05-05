<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Models\Category;
use App\Services\CloudinaryUploadService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Tag;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = "Restaurant info";

    /**
     * CREATE + EDIT modal — name only.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('id'),
                
                TextColumn::make('name')
                    ->searchable(),

                ImageColumn::make('img')
                    ->label('Photo')
                    ->url(fn($record) => $record->img)
                    ->imageSize(60)
                    ->circular(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),

                Action::make('photo')
                    ->label('Photo')
                    ->icon(Heroicon::OutlinedPhoto)
                    ->color('info')
                    ->modalHeading('Category Photo')
                    ->modalSubmitActionLabel('Save Photo')
                    // Pre-fill the modal with the existing image when opened
                    ->fillForm(fn(Category $record): array => [
                        'img' => $record->img ? [$record->img] : [],
                    ])
                    ->schema([
                        FileUpload::make('img')
                            ->label('Cover')
                            ->image()
                            ->required(false)
                            // Render the existing Cloudinary URL as a preview thumbnail
                            ->getUploadedFileUsing(function (string $file): array {
                                return [
                                    'name' => basename(parse_url($file, PHP_URL_PATH)),
                                    'size' => 0,
                                    'type' => 'image/jpeg',
                                    'url'  => $file,
                                ];
                            }),
                    ])
                    ->action(function (array $data, Category $record): void {
                        $uploaded = $data['img'] ?? null;
                        $file = is_array($uploaded)
                            ? collect($uploaded)->first()
                            : $uploaded;

                        // No new file — the existing URL came back unchanged
                        if (! $file || $file === $record->img) {
                            Notification::make()
                                ->title('No new photo selected.')
                                ->warning()
                                ->send();
                            return;
                        }

                        $uploader = new CloudinaryUploadService();

                        if ($record->img) {
                            $uploader->delete($record->img);
                        }

                        $url = $uploader->upload($file->getRealPath(), 'categories');

                        $record->update(['img' => $url]);

                        Notification::make()
                            ->title('Photo updated successfully.')
                            ->success()
                            ->send();
                    }),

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
            'index' => ManageCategories::route('/'),
        ];
    }
}