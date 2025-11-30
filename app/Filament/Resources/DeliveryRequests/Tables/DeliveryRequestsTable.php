<?php

namespace App\Filament\Resources\DeliveryRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Deliveries\Models\DeliveryRequest;

class DeliveryRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('#'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone_number')
                    ->label('Phone'),

                TextColumn::make('city')
                    ->sortable(),

                TextColumn::make('vehicle_type')
                    ->label('Vehicle Type'),

                TextColumn::make('vehicle_number')
                    ->label('Vehicle Number'),

                ImageColumn::make('personal_photo')
                    ->label('Photo')
                    ->getStateUsing(fn(DeliveryRequest $record) => asset(path: 'storage/' . $record->personal_photo))
                    ->circular(),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn(DeliveryRequest $record) => match ($record->status) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => 'Unknown',
                    })
                    ->colors([
                        'success' => 'Approved',
                        'warning' => 'Pending',
                        'danger' => 'Rejected',
                    ])
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime('Y-m-d')
                    ->label('Registered At')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
