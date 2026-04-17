<?php

namespace App\Filament\App\Resources\MenuItems\Schemas;

use App\Services\CloudinaryUploadService;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;
use Modules\Restaurants\Models\Category;
use Modules\Restaurants\Models\Menu;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Classification')
                    ->description('Assign this item to a menu and category.')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->options(Menu::ordered()->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->placeholder('— Select a menu —')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('category_id', null))
                            ->dehydrated(false),

                        Select::make('category_id')
                            ->label('Category')
                            ->required()
                            ->searchable()
                            ->options(function (Get $get): array {
                                $menuId = $get('menu_id');

                                return Category::query()
                                    ->when(
                                        filled($menuId),
                                        fn($q) => $q->where('menu_id', $menuId)
                                    )
                                    ->ordered()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->placeholder('— Select a category —'),

                        Actions::make([
                            Action::make('saveClassification')
                                ->label('Save')
                                ->icon('heroicon-o-check')
                                ->action(function (Get $get, $record) {
                                    $categoryId = $get('category_id');

                                    if (blank($categoryId)) {
                                        Notification::make()
                                            ->title('Please select a category first.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    $record->update([
                                        'category_id' => $categoryId,
                                    ]);

                                    Notification::make()
                                        ->title('Classification saved.')
                                        ->success()
                                        ->send();
                                })
                                ->hidden(fn($record) => $record === null), // hide on Create
                        ])->columnSpanFull(),
                    ]),

                Section::make('Item Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Item Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('price')
                            ->label('Base Price')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->minValue(0),

                        TextInput::make('preparation_time')
                            ->label('Prep Time (minutes)')
                            ->numeric()
                            ->nullable()
                            ->minValue(0)
                            ->suffix('min'),

                        TextInput::make('position')
                            ->label('Display Position')
                            ->numeric()
                            ->default(0),

                        Grid::make(1)
                            ->schema([
                                Toggle::make('is_available')
                                    ->label('Available')
                                    ->default(true),

                                Toggle::make('is_featured')
                                    ->label('Featured')
                                    ->default(false),
                            ]),

                        Actions::make([
                            Action::make('saveDetails')
                                ->label('Save')
                                ->icon('heroicon-o-check')
                                ->action(function (Get $get, $record) {
                                    $record->update([
                                        'name' => $get('name'),
                                        'description' => $get('description'),
                                        'price' => $get('price'),
                                        'preparation_time' => $get('preparation_time'),
                                        'position' => $get('position'),
                                        'is_available' => $get('is_available'),
                                        'is_featured' => $get('is_featured'),
                                    ]);

                                    Notification::make()
                                        ->title('Details saved.')
                                        ->success()
                                        ->send();
                                })
                                ->hidden(fn($record) => $record === null),
                        ])->columnSpanFull(),
                    ]),

                Section::make('Item Image')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Image')
                            ->image()
                            ->disk('local')          // temp disk, we handle the real upload manually
                            ->directory('tmp')
                            ->dehydrated(false),     // we save manually, don't let the form touch it

                        Action::make('saveImage')
                            ->label('Save Image')
                            ->icon('heroicon-o-photo')
                            ->action(function (Get $get, $record) {
                                $uploader = new CloudinaryUploadService();

                                $imageState = $get('image');

                                if (blank($imageState)) {
                                    Notification::make()
                                        ->title('No image selected.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                // State is an array keyed by UUID, value is a TemporaryUploadedFile object
                                /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile $tmpFile */
                                $tmpFile = collect($imageState)->first();

                                if (!$tmpFile || !$tmpFile->exists()) {
                                    Notification::make()
                                        ->title('Temporary file not found. Please re-select the image.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                // Get the real absolute path on disk
                                $realPath = $tmpFile->getRealPath();

                                if (!$realPath || !file_exists($realPath)) {
                                    // getRealPath() returns the PHP tmp path which may be gone —
                                    // fall back to the Livewire disk path instead
                                    $disk = Storage::disk(
                                        config('livewire.temporary_file_upload.disk', 'local')
                                    );
                                    $realPath = $disk->path($tmpFile->getFilename());
                                }

                                if (!file_exists($realPath)) {
                                    Notification::make()
                                        ->title('Could not resolve file path. Please re-select the image.')
                                        ->warning()
                                        ->send();
                                    return;
                                }

                                // Delete old Cloudinary image if exists
                                if ($record->image) {
                                    $uploader->delete($record->image);
                                }

                                // Upload to Cloudinary
                                $newImageUrl = $uploader->upload($realPath, 'menu-items');

                                // Persist
                                $record->update(['image' => $newImageUrl]);

                                Notification::make()
                                    ->title('Image saved successfully.')
                                    ->success()
                                    ->send();
                            })
                            ->hidden(fn($record) => $record === null),

                    ]),

            ]);
    }
}