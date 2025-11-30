<?php

namespace App\Filament\Owner\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('restaurant.name')
                    ->label('Restaurant')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('usd', true)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge() // يحوّل النص إلى badge
                    ->color(fn(string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'preparing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'primary',
                    })
                    ->formatStateUsing(fn($state) => Str::title(str_replace('_', ' ', $state))),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn(string $state): string => $state === 'paid' ? 'success' : 'danger')
                    ->formatStateUsing(fn($state) => Str::title($state)),

                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->sortable(),

                TextColumn::make('delivery_fee')
                    ->label('Delivery Fee')
                    ->money('usd', true)
                    ->sortable(),

                TextColumn::make('tax_amount')
                    ->label('Tax')
                    ->money('usd', true)
                    ->sortable(),

                TextColumn::make('preparation_time')
                    ->label('Prep Time (min)')
                    ->sortable(),

                TextColumn::make('assignedDriver.name')
                    ->label('Driver')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
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
