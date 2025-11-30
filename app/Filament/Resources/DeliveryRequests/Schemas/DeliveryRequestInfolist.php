<?php

namespace App\Filament\Resources\DeliveryRequests\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Deliveries\Models\DeliveryRequest;

class DeliveryRequestInfolist
{
    public static function configure(Schema $schema): Schema
{
    return $schema
        ->components([
            // =====================
            // DRIVER INFORMATION
            // =====================
            Section::make('Driver Information')
                ->description('Basic personal information of the driver')
                ->schema([
                    Grid::make()
                        ->columns(1)
                        ->schema([
                            Grid::make()
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('first_name')
                                        ->label('First Name')
                                        ->icon('heroicon-o-user'),

                                    TextEntry::make('last_name')
                                        ->label('Last Name')
                                        ->icon('heroicon-o-user'),
                                ]),

                            Grid::make()
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('phone_number')
                                        ->label('Phone Number')
                                        ->icon('heroicon-o-phone')
                                        ->url(fn($state) => "tel:{$state}"),

                                    TextEntry::make('national_id')
                                        ->label('National ID')
                                        ->icon('heroicon-o-identification'),

                                    TextEntry::make('city')
                                        ->label('City')
                                        ->icon('heroicon-o-map-pin'),
                                ]),

                            TextEntry::make('address')
                                ->label('Address')
                                ->icon('heroicon-o-home')
                                ->columnSpanFull(),
                        ]),
                ])
                ->icon('heroicon-o-user-circle')
                ->collapsible()
                ->collapsed(false),

            // =====================
            // VEHICLE INFORMATION
            // =====================
            Section::make('Vehicle Information')
                ->description('Details of the delivery vehicle')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            TextEntry::make('vehicle_type')
                                ->label('Vehicle Type')
                                ->icon('heroicon-o-truck'),

                            TextEntry::make('vehicle_number')
                                ->label('Vehicle Number')
                                ->icon('heroicon-o-clipboard-document'),
                        ]),
                ])
                ->icon('heroicon-o-truck')
                ->collapsible(),

            // =====================
            // STATUS & EXPERIENCE
            // =====================
            Section::make('Status & Experience')
                ->description('Request status and experience notes')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn(string $state): string => match ($state) {
                                    'approved' => 'success',
                                    'pending' => 'warning',
                                    'rejected' => 'danger',
                                    'under_review' => 'info',
                                    default => 'gray',
                                })
                                ->icon(fn(string $state): string => match ($state) {
                                    'approved' => 'heroicon-o-check-circle',
                                    'pending' => 'heroicon-o-clock',
                                    'rejected' => 'heroicon-o-x-circle',
                                    'under_review' => 'heroicon-o-eye',
                                    default => 'heroicon-o-question-mark-circle',
                                }),

                        ]),

                    TextEntry::make('experience_note')
                        ->label('Experience Notes')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->columnSpanFull()
                        ->placeholder('No notes available')
                        ->prose(),
                ])
                ->icon('heroicon-o-clipboard-document-list')
                ->collapsible(),

            // =====================
            // DOCUMENTS & PHOTOS
            // =====================
            Section::make('Documents & Photos')
                ->description('All required documents and photos')
                ->schema([
                    Grid::make()
                        ->columns(2)
                        ->schema([
                            // Personal Photo - Larger display
                            ImageEntry::make('personal_photo')
                                ->label('Personal Photo')
                                ->getStateUsing(fn($record) => self::getImageUrl($record->personal_photo))
                                ->extraImgAttributes([
                                    'class' => 'rounded-2xl shadow-lg hover:shadow-xl transition-shadow duration-300 object-cover w-full h-64'
                                ])
                                ->columnSpan(1),

                            // Documents Grid
                            Grid::make()
                                ->columns(2)
                                ->schema([
                                    ImageEntry::make('id_card_front')
                                        ->label('ID Card - Front')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->id_card_front))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-48'
                                        ]),

                                    ImageEntry::make('id_card_back')
                                        ->label('ID Card - Back')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->id_card_back))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-48'
                                        ]),

                                    ImageEntry::make('driving_license')
                                        ->label('Driving License')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->driving_license))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-48'
                                        ])
                                        ->columnSpan(2),
                                ])
                                ->columnSpan(1),
                        ]),

                    // Additional Documents Section
                    Section::make('Additional Documents')
                        ->schema([
                            Grid::make()
                                ->columns(3)
                                ->schema([
                                    ImageEntry::make('vehicle_license')
                                        ->label('Vehicle License')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->vehicle_license))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-40'
                                        ]),

                                    ImageEntry::make('insurance_certificate')
                                        ->label('Insurance Certificate')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->insurance_certificate))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-40'
                                        ]),

                                    ImageEntry::make('additional_document')
                                        ->label('Additional Document')
                                        ->getStateUsing(fn($record) => self::getImageUrl($record->additional_document))
                                        ->extraImgAttributes([
                                            'class' => 'rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 object-cover w-full h-40'
                                        ]),
                                ]),
                        ])
                        ->collapsible()
                        ->collapsed(fn($record) => !$record->vehicle_license && !$record->insurance_certificate),
                ])
                ->icon('heroicon-o-photo')
                ->collapsible()
                ->collapsed(false),
        ]);
}

// Helper method for image URLs
protected static function getImageUrl(?string $path): ?string
{
    if (!$path) {
        return null;
    }

    return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
}
}
