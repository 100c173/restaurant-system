<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ManageOrders;
use BackedEnum;
use Filament\Infolists\Components\RepeatableEntry;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderStatusLog;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 1;

    // ─── Helpers ──────────────────────────────────────────────────

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'confirmed', 'preparing' => 'info',
            'ready', 'delivered', 'completed' => 'success',
            'assigned', 'picked_up' => 'primary',
            'rejected', 'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            'pending' => 'heroicon-m-clock',
            'confirmed' => 'heroicon-m-check-circle',
            'preparing' => 'heroicon-m-fire',
            'ready' => 'heroicon-m-bell',
            'assigned' => 'heroicon-m-truck',
            'picked_up' => 'heroicon-m-arrow-up-tray',
            'delivered' => 'heroicon-m-map-pin',
            'completed' => 'heroicon-m-check-badge',
            'rejected' => 'heroicon-m-x-circle',
            'cancelled' => 'heroicon-m-no-symbol',
            default => 'heroicon-m-question-mark-circle',
        };
    }

    protected static array $statusOptions = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'rejected' => 'Rejected',
        'preparing' => 'Preparing',
        'ready' => 'Ready',
        'assigned' => 'Assigned',
        'picked_up' => 'Picked Up',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    // ─── Status Change Helper ─────────────────────────────────────

    protected static function applyStatusChange(Order $record, string $newStatus, ?string $note = null): void
    {
        if ($record->status === $newStatus)
            return;

        $timestampMap = [
            'confirmed' => 'confirmed_at',
            'ready' => 'ready_at',
            'picked_up' => 'dispatched_at',
            'delivered' => 'delivered_at',
        ];

        $updates = ['status' => $newStatus];

        if (isset($timestampMap[$newStatus])) {
            $updates[$timestampMap[$newStatus]] = now();
        }

        $record->update($updates);

        OrderStatusLog::create([
            'order_id' => $record->id,
            'status' => $newStatus,
            'changed_by_type' => 'system',
            'changed_by_id' => auth()->id(),
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    // ─── Edit Schema ──────────────────────────────────────────────

    public static function editSchema(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Status & Payment')
                ->columns(2)
                ->schema([
                    Select::make('status')->options(static::$statusOptions)->required(),
                    Select::make('payment_status')
                        ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded'])
                        ->required(),
                    Textarea::make('_status_note')
                        ->label('Note (optional)')
                        ->columnSpanFull()
                        ->dehydrated(false),
                ])
                ->footerActions([
                    Action::make('saveStatusPayment')
                        ->label('Save')
                        ->action(function (Get $get, $record) {
                            static::applyStatusChange($record, $get('status'), $get('_status_note'));
                            $record->update(['payment_status' => $get('payment_status')]);
                            Notification::make()->title('Status & payment updated.')->success()->send();
                        }),
                ])
                ->footerActionsAlignment(\Filament\Support\Enums\Alignment::End),

            Section::make('Delivery Info')
                ->columns(2)
                ->visible(fn(Get $get) => $get('type') === 'delivery')
                ->schema([
                    Textarea::make('delivery_address')->columnSpanFull(),
                    TextInput::make('delivery_lat')->label('Latitude')->numeric(),
                    TextInput::make('delivery_lng')->label('Longitude')->numeric(),
                ])
                ->footerActions([
                    Action::make('saveDelivery')
                        ->label('Save')
                        ->action(function (Get $get, $record) {
                            $record->update([
                                'delivery_address' => $get('delivery_address'),
                                'delivery_lat' => $get('delivery_lat'),
                                'delivery_lng' => $get('delivery_lng'),
                            ]);
                            Notification::make()->title('Delivery info updated.')->success()->send();
                        }),
                ])
                ->footerActionsAlignment(\Filament\Support\Enums\Alignment::End),

            Section::make('Special Instructions')
                ->schema([
                    Textarea::make('special_instructions')->hiddenLabel()->rows(3),
                ])
                ->footerActions([
                    Action::make('saveInstructions')
                        ->label('Save')
                        ->action(function (Get $get, $record) {
                            $record->update(['special_instructions' => $get('special_instructions')]);
                            Notification::make()->title('Instructions updated.')->success()->send();
                        }),
                ])
                ->footerActionsAlignment(\Filament\Support\Enums\Alignment::End),
        ]);
    }

    // ─── Table ────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('placed_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->copyable(),

                TextColumn::make('restaurant_name')
                    ->label('Restaurant')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'delivery' => 'info',
                        'pickup' => 'warning',
                        'dine_in' => 'success',
                    })
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => static::statusColor($state))
                    ->icon(fn($state) => static::statusIcon($state))
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                    })
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                TextColumn::make('total')
                    ->money('USD')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                TextColumn::make('placed_at')
                    ->label('Placed')
                    ->dateTime('M d, H:i')
                    ->sortable(),
            ])

            // ── Filters: hidden until the filter button is clicked ─
            ->filters([
                SelectFilter::make('restaurant_name')
                    ->label('Restaurant')
                    ->options(fn() => Order::query()->distinct()->pluck('restaurant_name', 'restaurant_name'))
                    ->searchable(),

                SelectFilter::make('status')
                    ->options(static::$statusOptions)
                    ->multiple(),

                SelectFilter::make('type')
                    ->options(['delivery' => 'Delivery', 'pickup' => 'Pickup', 'dine_in' => 'Dine In']),

                SelectFilter::make('payment_status')
                    ->label('Payment Status')
                    ->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed', 'refunded' => 'Refunded']),

                Filter::make('placed_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(
                        fn($query, array $data) => $query
                            ->when($data['from'], fn($q, $v) => $q->whereDate('placed_at', '>=', $v))
                            ->when($data['until'], fn($q, $v) => $q->whereDate('placed_at', '<=', $v))
                    ),
            ])
            // Filters are hidden; revealed by the built-in filter button in the toolbar
            ->filtersFormColumns(2)

            // ── Record Actions: grouped by workflow ───────────────
            ->recordActions([

                // Primary: quick status bump
                Action::make('changeStatus')
                    ->label('Update Status')
                    ->icon('heroicon-m-arrow-path')
                    ->color('primary')
                    ->modalWidth('md')
                    ->modalHeading('Update Order Status')
                    ->form([
                        Select::make('status')
                            ->options(static::$statusOptions)
                            ->default(fn(Order $record) => $record->status)
                            ->required(),
                        Textarea::make('note')->label('Note (optional)')->rows(2),
                    ])
                    ->action(function (Order $record, array $data) {
                        static::applyStatusChange($record, $data['status'], $data['note'] ?? null);
                        Notification::make()->title('Status updated.')->success()->send();
                    }),

                // Secondary: everything else in a dropdown
                ActionGroup::make([

                    ViewAction::make()
                        ->label('View Details')
                        ->modalWidth('3xl')
                        ->infolist([
                            Section::make('Summary')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('reference_number')->copyable()->weight(FontWeight::Bold),
                                    TextEntry::make('restaurant_name'),
                                    TextEntry::make('placed_at')->dateTime('M d Y, H:i'),
                                    TextEntry::make('type')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'delivery' => 'info',
                                            'pickup' => 'warning',
                                            'dine_in' => 'success',
                                        })
                                        ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn($state) => static::statusColor($state))
                                        ->icon(fn($state) => static::statusIcon($state))
                                        ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),
                                    TextEntry::make('payment_status')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'paid' => 'success',
                                            'pending' => 'warning',
                                            'failed' => 'danger',
                                            'refunded' => 'info',
                                        })
                                        ->formatStateUsing(fn($state) => ucfirst($state)),
                                ]),

                            Section::make('Financials')
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('subtotal')->money('USD'),
                                    TextEntry::make('delivery_fee')->money('USD'),
                                    TextEntry::make('discount_amount')->money('USD'),
                                    TextEntry::make('total')->money('USD')->weight(FontWeight::Bold),
                                ]),

                            Section::make('Delivery')
                                ->columns(2)
                                ->visible(fn($record) => $record->isDelivery())
                                ->schema([
                                    TextEntry::make('delivery_address')->columnSpanFull(),
                                    TextEntry::make('delivery_lat')->label('Latitude'),
                                    TextEntry::make('delivery_lng')->label('Longitude'),
                                ]),

                            Section::make('Special Instructions')
                                ->visible(fn($record) => filled($record->special_instructions))
                                ->schema([
                                    TextEntry::make('special_instructions')->hiddenLabel(),
                                ]),
                        ]),

                    EditAction::make()
                        ->label('Edit Order')
                        ->schema(fn(Schema $schema): Schema => static::editSchema($schema))
                        ->modalSubmitAction(false)
                        ->modalWidth('3xl'),

                    Action::make('viewTimeline')
                        ->label('Status Timeline')
                        ->icon('heroicon-o-clock')
                        ->color('gray')
                        ->modalHeading('Status Timeline')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->infolist(fn(Order $record) => [
                            RepeatableEntry::make('statusLogs')
                                ->hiddenLabel()
                                ->columns(4)
                                ->schema([
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn($state) => static::statusColor($state))
                                        ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),
                                    TextEntry::make('changed_by_type')
                                        ->label('By')
                                        ->formatStateUsing(fn($state) => ucfirst($state)),
                                    TextEntry::make('note')
                                        ->placeholder('—'),
                                    TextEntry::make('created_at')
                                        ->dateTime('M d, H:i'),
                                ]),
                        ]),

                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->tooltip('More actions'),
            ])

            // ── Toolbar bulk actions ───────────────────────────────
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulkUpdateStatus')
                        ->label('Update Status')
                        ->icon('heroicon-m-arrow-path')
                        ->form([
                            Select::make('status')
                                ->options([
                                    'confirmed' => 'Confirmed',
                                    'rejected' => 'Rejected',
                                    'preparing' => 'Preparing',
                                    'ready' => 'Ready',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                ])
                                ->required(),
                            Textarea::make('note')->rows(2)->label('Note (optional)'),
                        ])
                        ->action(function ($records, array $data) {
                            $records->each(
                                fn(Order $record) =>
                                static::applyStatusChange($record, $data['status'], $data['note'] ?? null)
                            );
                            Notification::make()->title('Orders updated.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageOrders::route('/'),
        ];
    }
}