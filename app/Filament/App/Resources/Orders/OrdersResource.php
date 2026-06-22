<?php

namespace App\Filament\App\Resources\Orders;

use App\Filament\App\Resources\Orders\Pages\ManageOrders;
use BackedEnum;
use Modules\Orders\Events\ChangeOrderStatus;
use Modules\Orders\Events\OrderStatusChanged;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Modules\Orders\Models\TenantOrder;

class OrdersResource extends Resource
{
    protected static ?string $model = TenantOrder::class;

    protected static ?string $recordTitleAttribute = 'reference_number';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Orders';

    protected static ?string $navigationLabel = 'Orders';

    // ─── Helpers ──────────────────────────────────────────────────

    public static function statusColor(string $status): string
    {
        return match ($status) {
            'pending'   => 'warning',
            'confirmed' => 'info',
            'preparing' => 'primary',
            'ready'     => 'success',
            'rejected'  => 'danger',
            default     => 'gray',
        };
    }

    public static function statusIcon(string $status): string
    {
        return match ($status) {
            'pending'   => 'heroicon-m-clock',
            'confirmed' => 'heroicon-m-check-circle',
            'preparing' => 'heroicon-m-fire',
            'ready'     => 'heroicon-m-bell',
            'rejected'  => 'heroicon-m-x-circle',
            default     => 'heroicon-m-question-mark-circle',
        };
    }

    protected static array $statusOptions = [
        'pending'   => 'Pending',
        'confirmed' => 'Confirmed',
        'preparing' => 'Preparing',
        'ready'     => 'Ready',
        'rejected'  => 'Rejected',
    ];

    // ─── Status Change Helper ─────────────────────────────────────

    protected static function applyStatusChange(TenantOrder $record, string $newStatus): void
    {
        if ($record->status === $newStatus) return;

        $updates = ['status' => $newStatus];

        if ($newStatus === 'confirmed') $updates['confirmed_at'] = now();
        if ($newStatus === 'ready')     $updates['ready_at']     = now();

        $record->update($updates);
        ChangeOrderStatus::dispatch($record);
        OrderStatusChanged::dispatch($record->fresh()); // broadcast to Flutter
    }

    // ─── Recalculate order totals after item edits ────────────────

    protected static function recalculateTotals(TenantOrder $record): void
    {
        $record->refresh();

        $subtotal = $record->items->sum(function ($item) {
            $modifiersTotal = $item->modifiers->sum('price');
            return ($item->unit_price + $modifiersTotal) * $item->quantity;
        });

        $record->update(['subtotal' => $subtotal, 'total' => $subtotal]);
    }

    // ─── Edit Items Schema ────────────────────────────────────────

    public static function editItemsSchema(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Order Items')
                ->schema([
                    Repeater::make('items')
                        ->label('')
                        ->relationship('items')
                        ->schema([
                            TextInput::make('item_name')
                                ->label('Item')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('variant_name')
                                ->label('Variant')
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('—'),

                            TextInput::make('quantity')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    $set('line_total', round($get('unit_price') * (int) $state, 2));
                                }),

                            TextInput::make('line_total')
                                ->label('Total')
                                ->prefix('$')
                                ->disabled()
                                ->dehydrated(true),

                            TextInput::make('special_note')
                                ->label('Note')
                                ->placeholder('e.g. no onions')
                                ->columnSpanFull(),

                            // Hidden — needed for recalculation & persistence
                            Hidden::make('unit_price'),
                            Hidden::make('order_id'),
                            Hidden::make('item_id'),
                            Hidden::make('variant_id'),
                        ])
                        ->columns(4)
                        ->deletable(true)
                        ->reorderable(false)
                        ->addable(false)
                        ->deleteAction(
                            fn(Action $action) => $action
                                ->requiresConfirmation()
                                ->modalHeading('Remove this item?')
                                ->modalDescription('This will permanently remove the item from the order.')
                        ),
                ]),

            Section::make('Special Instructions')
                ->schema([
                    Textarea::make('special_instructions')
                        ->hiddenLabel()
                        ->rows(2)
                        ->placeholder('Add or update any special instructions…'),
                ]),
        ]);
    }

    // ─── Form (required by resource) ─────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    // ─── Table ────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('20s')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->copyable(),

                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'delivery' => 'info',
                        'pickup'   => 'warning',
                        'dine_in'  => 'success',
                    })
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => static::statusColor($state))
                    ->icon(fn($state) => static::statusIcon($state))
                    ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),

                TextColumn::make('total')
                    ->money('USD')
                    ->weight(FontWeight::Bold),

                TextColumn::make('created_at')
                    ->label('Placed')
                    ->dateTime('M d, H:i')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->options(static::$statusOptions)
                    ->multiple(),

                SelectFilter::make('type')
                    ->options([
                        'delivery' => 'Delivery',
                        'pickup'   => 'Pickup',
                        'dine_in'  => 'Dine In',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn($query, array $data) => $query
                        ->when($data['from'], fn($q, $v) => $q->whereDate('created_at', '>=', $v))
                        ->when($data['until'], fn($q, $v) => $q->whereDate('created_at', '<=', $v))
                    ),
            ])
            ->filtersFormColumns(2)

            ->recordActions([

                // ── Primary: lifecycle progression ────────────────────
                Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-m-check-circle')
                    ->color('info')
                    ->visible(fn(TenantOrder $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm this order?')
                    ->modalDescription('The customer will be notified that their order is confirmed.')
                    ->action(function (TenantOrder $record) {
                        static::applyStatusChange($record, 'confirmed');
                        Notification::make()->title('Order confirmed.')->success()->send();
                    }),

                Action::make('markPreparing')
                    ->label('Preparing')
                    ->icon('heroicon-m-fire')
                    ->color('primary')
                    ->visible(fn(TenantOrder $record) => $record->status === 'confirmed')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as preparing?')
                    ->action(function (TenantOrder $record) {
                        static::applyStatusChange($record, 'preparing');
                        Notification::make()->title('Order is now being prepared.')->success()->send();
                    }),

                Action::make('markReady')
                    ->label('Ready')
                    ->icon('heroicon-m-bell')
                    ->color('success')
                    ->visible(fn(TenantOrder $record) => $record->status === 'preparing')
                    ->requiresConfirmation()
                    ->modalHeading('Mark as ready?')
                    ->modalDescription('This will notify the customer / driver that the order is ready.')
                    ->action(function (TenantOrder $record) {
                        static::applyStatusChange($record, 'ready');
                        Notification::make()->title('Order is ready.')->success()->send();
                    }),

                // ── Secondary: view, edit, reject ─────────────────────
                ActionGroup::make([

                    ViewAction::make()
                        ->label('View Order')
                        ->modalWidth('2xl')
                        ->infolist([
                            Section::make('Order Info')
                                ->columns(3)
                                ->schema([
                                    TextEntry::make('reference_number')
                                        ->label('Reference')
                                        ->copyable()
                                        ->weight(FontWeight::Bold),
                                    TextEntry::make('type')
                                        ->badge()
                                        ->color(fn($state) => match ($state) {
                                            'delivery' => 'info',
                                            'pickup'   => 'warning',
                                            'dine_in'  => 'success',
                                        })
                                        ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn($state) => static::statusColor($state))
                                        ->icon(fn($state) => static::statusIcon($state))
                                        ->formatStateUsing(fn($state) => str($state)->replace('_', ' ')->title()),
                                    TextEntry::make('customer_name')->label('Customer'),
                                    TextEntry::make('customer_phone')->label('Phone')->copyable(),
                                    TextEntry::make('table_number')
                                        ->label('Table')
                                        ->placeholder('—')
                                        ->visible(fn($record) => $record->type === 'dine_in'),
                                    TextEntry::make('delivery_address')
                                        ->label('Delivery Address')
                                        ->columnSpanFull()
                                        ->placeholder('—')
                                        ->visible(fn($record) => $record->type === 'delivery'),
                                ]),

                            Section::make('Items')
                                ->schema([
                                    RepeatableEntry::make('items')
                                        ->hiddenLabel()
                                        ->columns(4)
                                        ->schema([
                                            TextEntry::make('item_name')
                                                ->label('Item')
                                                ->weight(FontWeight::SemiBold),
                                            TextEntry::make('variant_name')
                                                ->label('Variant')
                                                ->placeholder('—'),
                                            TextEntry::make('quantity')
                                                ->label('Qty'),
                                            TextEntry::make('line_total')
                                                ->label('Total')
                                                ->money('USD'),
                                            RepeatableEntry::make('modifiers')
                                                ->label('Add-ons')
                                                ->columnSpanFull()
                                                ->columns(3)
                                                ->hidden(fn($record) => $record->modifiers->isEmpty())
                                                ->schema([
                                                    TextEntry::make('modifier_group_name')->label('Group'),
                                                    TextEntry::make('modifier_name')->label('Option'),
                                                    TextEntry::make('price')->money('USD')->label('Price'),
                                                ]),
                                            TextEntry::make('special_note')
                                                ->label('Note')
                                                ->placeholder('—')
                                                ->columnSpanFull()
                                                ->visible(fn($record) => filled($record->special_note)),
                                        ]),
                                ]),

                            Section::make('Totals')
                                ->columns(2)
                                ->schema([
                                    TextEntry::make('subtotal')->money('USD'),
                                    TextEntry::make('total')->money('USD')->weight(FontWeight::Bold),
                                ]),

                            Section::make('Special Instructions')
                                ->visible(fn($record) => filled($record->special_instructions))
                                ->schema([
                                    TextEntry::make('special_instructions')->hiddenLabel(),
                                ]),

                            Section::make('Timeline')
                                ->columns(3)
                                ->collapsed()
                                ->schema([
                                    TextEntry::make('created_at')->label('Placed')->dateTime('M d, H:i'),
                                    TextEntry::make('confirmed_at')->label('Confirmed')->dateTime('M d, H:i')->placeholder('—'),
                                    TextEntry::make('ready_at')->label('Ready')->dateTime('M d, H:i')->placeholder('—'),
                                ]),
                        ]),

                    EditAction::make('editItems')
                        ->label('Edit Items')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->visible(fn(TenantOrder $record) => in_array($record->status, ['pending', 'confirmed', 'preparing']))
                        ->schema(fn(Schema $schema): Schema => static::editItemsSchema($schema))
                        ->modalHeading('Edit Order Items')
                        ->modalWidth('3xl')
                        ->modalSubmitActionLabel('Save Changes')
                        ->using(function (TenantOrder $record, array $data) {
                            $record->update([
                                'special_instructions' => $data['special_instructions'] ?? $record->special_instructions,
                            ]);

                            static::recalculateTotals($record);

                            Notification::make()->title('Order updated.')->success()->send();

                            return $record;
                        }),

                    Action::make('reject')
                        ->label('Reject Order')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->visible(fn(TenantOrder $record) => in_array($record->status, ['pending', 'confirmed']))
                        ->form([
                            Textarea::make('reason')
                                ->label('Reason for rejection (optional)')
                                ->rows(2),
                        ])
                        ->action(function (TenantOrder $record) {
                            static::applyStatusChange($record, 'rejected');
                            Notification::make()->title('Order rejected.')->danger()->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Reject this order?')
                        ->modalDescription('This action cannot be undone.'),

                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->tooltip('More'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('bulkConfirm')
                        ->label('Confirm Selected')
                        ->icon('heroicon-m-check-circle')
                        ->color('info')
                        ->action(function ($records) {
                            $records->each(function (TenantOrder $record) {
                                if ($record->status === 'pending') {
                                    static::applyStatusChange($record, 'confirmed');
                                }
                            });
                            Notification::make()->title('Orders confirmed.')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    \Filament\Actions\BulkAction::make('bulkReject')
                        ->label('Reject Selected')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function (TenantOrder $record) {
                                if (in_array($record->status, ['pending', 'confirmed'])) {
                                    static::applyStatusChange($record, 'rejected');
                                }
                            });
                            Notification::make()->title('Orders rejected.')->danger()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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