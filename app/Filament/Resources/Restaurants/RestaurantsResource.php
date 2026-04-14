<?php

namespace App\Filament\Resources\Restaurants;

use App\Filament\Resources\Restaurants\Pages\ManageRestaurants;
use App\Services\CloudinaryUploadService;
use BackedEnum;
use BladeUI\Icons\Components\Icon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Modules\Restaurants\Models\Restaurant;
use UnitEnum;

class RestaurantsResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = "Restaurant info";

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Basic Information')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('custom_name'),
                    Textarea::make('description')->columnSpanFull(),
                ]),

            Section::make('Media')
                ->columns(2)
                ->schema([
                    FileUpload::make('logo')->image()
                        ->saveUploadedFileUsing(function ($file, $record) {
                            $uploader = new CloudinaryUploadService();

                            // Delete old image if exists
                            if ($record?->logo) {
                                $uploader->delete($record->logo);
                            }
                            return $uploader->upload($file->getRealPath(), 'restaurants');
                        }),
                    FileUpload::make('cover_image')->image()
                        ->saveUploadedFileUsing(function ($file, $record) {
                            $uploader = new CloudinaryUploadService();

                            // Delete old image if exists
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
                    TextInput::make('commission_rate')->numeric()->suffix('%'),
                    Toggle::make('is_active')->label('Active'),
                ]),
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

                // Owner Info Action — view-only modal
                Action::make('ownerInfo')
                    ->label('Owner')
                    ->icon('heroicon-o-user')
                    ->color('gray')
                    ->modalHeading('Owner Information')
                    ->modalSubmitAction(false)           // no submit button
                    ->modalCancelActionLabel('Close')
                    ->infolist([                          // read-only infolist inside modal
                        TextEntry::make('owner.name')
                            ->label('Name')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('owner.email')
                            ->label('Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),
                        TextEntry::make('owner.phone')
                            ->label('Phone')
                            ->icon('heroicon-o-phone')
                            ->copyable(),
                    ]),

                // Categories Action — view-only modal with badges
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

                EditAction::make(),
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
