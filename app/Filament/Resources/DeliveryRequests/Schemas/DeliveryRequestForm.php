<?php

namespace App\Filament\Resources\DeliveryRequests\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DeliveryRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make("Edit Status")
                    ->schema([
                        Select::make('status')
                            ->label("Status")
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default("pending")
                         //   ->disabled(fn($record) => $record &&  in_array($record->status, ['approved', 'rejected']))
                            ->live()
                            ->required(),

                        Textarea::make("cancel_reason")
                            ->label("Cancellation Reason")
                            ->visible(fn(Get $get) => $get('status') === "rejected")
                            ->requiredIf('status', 'rejected'),

                        Textarea::make("admin_note")
                            ->label("Accepted Reason")
                            ->visible(fn(Get $get) => $get('status') === "approved")
                            ->requiredIf('status', 'approved'),

                    ]),
            ]);
    }
}
