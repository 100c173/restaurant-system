<?php

namespace App\Filament\Resources\Restaurants;

use App\Filament\Resources\Restaurants\Pages\ManageRestaurants;
use App\Services\CloudinaryUploadService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Restaurants\Models\Restaurant;
use UnitEnum;

class RestaurantsResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = "Restaurant info";

    /**
     * CREATE modal
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Information')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required(),
                    Textarea::make('description')->columnSpanFull(),
                ]),

            Section::make('Media')
                ->columns(2)
                ->schema([
                    FileUpload::make('logo')->image()
                        ->saveUploadedFileUsing(function ($file, $record) {
                            $uploader = new CloudinaryUploadService();
                            if ($record?->logo) {
                                $uploader->delete($record->logo);
                            }
                            return $uploader->upload($file->getRealPath(), 'restaurants');
                        }),

                    FileUpload::make('cover_image')->image()
                        ->saveUploadedFileUsing(function ($file, $record) {
                            $uploader = new CloudinaryUploadService();
                            if ($record?->cover_image) {
                                $uploader->delete($record->cover_image);
                            }
                            return $uploader->upload($file->getRealPath(), 'restaurants');
                        }),
                ]),

            Section::make('Contact & Location')
                ->columns(2)
                ->schema([
                    TextInput::make('address')->columnSpanFull()->copyable(),
                    TextInput::make('phone')->copyable(),
                    TextInput::make('email')->email()->copyable(),
                    TextInput::make('latitude')->numeric()->copyable(),
                    TextInput::make('longitude')->numeric()->copyable(),
                ]),

            Section::make('Hours & Settings')
                ->columns(2)
                ->schema([
                    TimePicker::make('opening_time'),
                    TimePicker::make('closing_time'),
                    Toggle::make('is_active')->label('Active'),
                ]),
        ]);
    }

    /**
     * EDIT modal — four independent sections, each with its own Save button.
     */
    public static function editSchema(Schema $schema): Schema
    {
        return $schema->components([

            // ── Section 1: Basic Information ─────────────────────────────
            Section::make('Basic Information')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required(),
                    Textarea::make('description')->columnSpanFull(),
                ])
                ->footerActions([
                    Action::make('saveBasicInfo')
                        ->label('Save Basic Info')
                        ->action(function (Get $get, $record) {
                            $record->update([
                                'name' => $get('name'),
                                'description' => $get('description'),
                            ]);

                            Notification::make()
                                ->title('Basic information saved successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            // ── Section 2: Logo ───────────────────────────────────────────
            Section::make('Logo')
                ->schema([
                    FileUpload::make('logo')
                        ->image()
                        ->label('Logo')
                        ->required(false)
                        ->afterStateHydrated(function (FileUpload $component, $record) {
                            if ($record?->logo) {
                                $component->state([$record->logo]);
                            }
                        })
                        ->getUploadedFileUsing(function (string $file): array {
                            return [
                                'name' => basename(parse_url($file, PHP_URL_PATH)),
                                'size' => 0,
                                'type' => 'image/jpeg',
                                'url' => $file,
                            ];
                        })
                        ->dehydrated(false),
                ])
                ->footerActions([
                    Action::make('saveLogo')
                        ->label('Save Logo')
                        ->action(function (Get $get, $record) {
                            $uploaded = $get('logo');

                            $file = is_array($uploaded)
                                ? collect($uploaded)->first()
                                : $uploaded;

                            if (!$file || $file === $record->logo) {
                                Notification::make()
                                    ->title('No new logo selected.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $uploader = new CloudinaryUploadService();

                            if ($record->logo) {
                                $uploader->delete($record->logo);
                            }

                            $url = $uploader->upload($file->getRealPath(), 'restaurants');
                            $record->update(['logo' => $url]);

                            Notification::make()
                                ->title('Logo updated successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            // ── Section 3: Cover Image ────────────────────────────────────
            Section::make('Cover Image')
                ->schema([
                    FileUpload::make('cover_image')
                        ->image()
                        ->label('Cover Image')
                        ->required(false)
                        ->afterStateHydrated(function (FileUpload $component, $record) {
                            if ($record?->cover_image) {
                                $component->state([$record->cover_image]);
                            }
                        })
                        ->getUploadedFileUsing(function (string $file): array {
                            return [
                                'name' => basename(parse_url($file, PHP_URL_PATH)),
                                'size' => 0,
                                'type' => 'image/jpeg',
                                'url' => $file,
                            ];
                        })
                        ->dehydrated(false),
                ])
                ->footerActions([
                    Action::make('saveCoverImage')
                        ->label('Save Cover Image')
                        ->action(function (Get $get, $record) {
                            $uploaded = $get('cover_image');

                            $file = is_array($uploaded)
                                ? collect($uploaded)->first()
                                : $uploaded;

                            if (!$file || $file === $record->cover_image) {
                                Notification::make()
                                    ->title('No new cover image selected.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $uploader = new CloudinaryUploadService();

                            if ($record->cover_image) {
                                $uploader->delete($record->cover_image);
                            }

                            $url = $uploader->upload($file->getRealPath(), 'restaurants');
                            $record->update(['cover_image' => $url]);

                            Notification::make()
                                ->title('Cover image updated successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            Section::make('Sham Cach Account')
                ->schema([
                    FileUpload::make('sham_cach_account_barcode')
                        ->image()
                        ->label('Sham Cach Account')
                        ->required(false)
                        ->afterStateHydrated(function (FileUpload $component, $record) {
                            if ($record?->sham_cach_account_barcode) {
                                $component->state([$record->sham_cach_account_barcode]);
                            }
                        })
                        ->getUploadedFileUsing(function (string $file): array {
                            return [
                                'name' => basename(parse_url($file, PHP_URL_PATH)),
                                'size' => 0,
                                'type' => 'image/jpeg',
                                'url' => $file,
                            ];
                        })
                        ->dehydrated(false),


                    TextInput::make('sham_cach_account_id')
                        ->label('Account ID')
                        ->columnSpanFull()
                        ->copyable(),
                ])
                ->footerActions([
                    Action::make('saveShamCachAccount')
                        ->label('Save Sham Cach Account')
                        ->action(function (Get $get, $record) {

                            $uploaded = $get('sham_cach_account_barcode');
                            $sham_cach_account_id = $get('sham_cach_account_id');

                            $file = is_array($uploaded)
                                ? collect($uploaded)->first()
                                : $uploaded;

                            if (
                                (!$file || $file === $record->sham_cach_account_barcode)
                                && $sham_cach_account_id === $record->sham_cach_account_id
                            ) {

                                Notification::make()
                                    ->title('No changes detected.')
                                    ->warning()
                                    ->send();
                                return;
                            }

                            $uploader = new CloudinaryUploadService();

                            if ($file && $file !== $record->sham_cach_account_barcode) {
                                if ($record->sham_cach_account_barcode) {
                                    $uploader->delete($record->sham_cach_account_barcode);
                                }

                                $url = $uploader->upload($file->getRealPath(), 'shamCash');
                                $record->sham_cach_account_barcode = $url;
                            }


                            $record->sham_cach_account_id = $sham_cach_account_id;
                            $record->save();

                            Notification::make()
                                ->title('Sham Cach Account updated successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            // ── Section 4: Contact & Location ─────────────────────────────
            Section::make('Contact & Location')
                ->columns(2)
                ->schema([
                    TextInput::make('address')->columnSpanFull()->copyable(),
                    TextInput::make('phone')->copyable(),
                    TextInput::make('email')->email()->copyable(),
                    TextInput::make('latitude')->numeric()->copyable(),
                    TextInput::make('longitude')->numeric()->copyable(),
                ])
                ->footerActions([
                    Action::make('saveContact')
                        ->label('Save Contact & Location')
                        ->action(function (Get $get, $record) {
                            $record->update([
                                'address' => $get('address'),
                                'phone' => $get('phone'),
                                'email' => $get('email'),
                                'latitude' => $get('latitude'),
                                'longitude' => $get('longitude'),
                            ]);

                            Notification::make()
                                ->title('Contact & location saved successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),

            // ── Section 5: Hours & Settings ───────────────────────────────
            Section::make('Hours & Settings')
                ->columns(2)
                ->schema([
                    TimePicker::make('opening_time'),
                    TimePicker::make('closing_time'),
                    Toggle::make('is_active')->label('Active'),
                ])
                ->footerActions([
                    Action::make('saveHoursSettings')
                        ->label('Save Hours & Settings')
                        ->action(function (Get $get, $record) {
                            $record->update([
                                'opening_time' => $get('opening_time'),
                                'closing_time' => $get('closing_time'),
                                'is_active' => $get('is_active'),
                            ]);

                            Notification::make()
                                ->title('Hours & settings saved successfully.')
                                ->success()
                                ->send();
                        }),
                ])
                ->footerActionsAlignment(Alignment::End),


        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                ImageColumn::make('logo')
                    ->circular(),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable(),

                TextColumn::make('address')
                    ->limit(30)
                    ->tooltip(fn($record) => $record->address),

                TextColumn::make('opening_time')
                    ->label('Opens')
                    ->time('H:i'),

                TextColumn::make('closing_time')
                    ->label('Closes')
                    ->time('H:i'),

                TextColumn::make('rate')
                    ->label('Rating')
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 4 => 'success',
                        $state >= 2.5 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('rate')
                    ->label('Filter by Rating')
                    ->options([
                        '6' => '⭐⭐⭐⭐⭐⭐ (6)',
                        '5' => '⭐⭐⭐⭐⭐ (5)',
                        '4' => '⭐⭐⭐⭐ (4+)',
                        '3' => '⭐⭐⭐ (3+)',
                        '2' => '⭐⭐ (2+)',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value']) {
                            $query->where('rate', '>=', $state['value']);
                        }
                    }),

                SelectFilter::make('is_active')
                    ->label('Filter by Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ]),
            ])
            ->recordActions([

                Action::make('ownerInfo')
                    ->label('Owner')
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->modalHeading('Owner Information')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->infolist([
                        TextEntry::make('tenant.owner.name')
                            ->label('Name')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('tenant.owner.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('tenant.owner.phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                    ]),

                Action::make('categories')
                    ->label('Categories')
                    ->icon('heroicon-o-tag')
                    ->color('info')
                    ->modalHeading('Restaurant Categories')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn($record) => view(
                        'filament.modals.restaurant-categories',
                        ['categories' => $record->categories]
                    )),

                EditAction::make()
                    ->schema(fn(Schema $schema): Schema => static::editSchema($schema))
                    ->modalSubmitAction(false),

                //DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageRestaurants::route('/'),
        ];
    }
}
