<?php

namespace App\Filament\Resources\RestaurantRequests\Tables;

use App\Filament\Resources\RestaurantRequests\RestaurantRequestsResource;
use App\Models\User;
use Event;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Modules\Restaurants\Events\RestaurantApproved;
use Modules\Restaurants\Models\RestaurantRequest;

class RestaurantRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make("restaurant_name")
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make("owner_name")
                    ->searchable()
                    ->sortable(),

                TextColumn::make("owner_email")
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                TextColumn::make("owner_phone")
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-phone'),

                TextColumn::make("address")
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->searchable(),

                TextColumn::make('created_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->label('Status'),
            ])
            ->actions([

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Restaurant Request')
                    ->modalDescription('Are you sure you want to approve this restaurant request? This will create a tenant database and user account.')
                    ->modalSubmitActionLabel('Yes, approve')
                    ->action(function (RestaurantRequest $record) {

                        $record->update(['status' => 'approved']);
                        Event::dispatch(new RestaurantApproved($record));


                        Notification::make()
                            ->title('Restaurant Approved')
                            ->body('Restaurant request has been approved successfully.')
                            ->success()
                            ->send();
                    }),

                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Reject Restaurant Request')
                    ->modalDescription('Are you sure you want to reject this restaurant request? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, reject')
                    ->action(function (RestaurantRequest $record) {
                        $record->update(['status' => 'rejected']);

                        Notification::make()
                            ->title('Restaurant Rejected')
                            ->body('Restaurant request has been rejected.')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No restaurant requests')
            ->emptyStateDescription('When restaurant requests are submitted, they will appear here.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->deferLoading()
            ->persistFiltersInSession();
    }
}