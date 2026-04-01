<?php

namespace App\Filament\Resources\RestaurantRequests\Tables;


use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Modules\Restaurants\Events\RestaurantApproved;



class RestaurantRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->sortable(),

                TextColumn::make('restaurant_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('address')
                    ->limit(40),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])

            ->filters([

                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])

            ])

            ->actions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        //->visible(fn($record) => $record->status === 'pending')
                        ->action(function ($record) {
                            // Use database transaction to ensure both operations succeed or fail together

                            // Dispatch the event - if this throws an exception, the transaction will rollback
                            event(new RestaurantApproved($record));

                        }),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-mark')
                        ->color('warning')
                       // ->visible(fn($record) => $record->status === 'pending')
                        ->form([
                            Textarea::make('cancel_reason')
                                ->label('Reason')
                                ->required()
                        ])
                        ->action(function ($record, $data) {
                            $record->update([
                                'status' => 'rejected',
                                'cancel_reason' => $data['cancel_reason']
                            ]);
                        }),

                    Action::make('pending')
                        ->label('Pending')
                        ->color('warning')
                       // ->visible(fn($record) => $record->status === 'pending')
                        ->action(function ($record, $data) {
                            $record->update([
                                'status' => 'pending',
                            ]);
                        }),
                ])
                    ->label('Actions')
                    ->icon('heroicon-o-chevron-down')
                    ->color('gray')
                    ->button(),

            ])

            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}