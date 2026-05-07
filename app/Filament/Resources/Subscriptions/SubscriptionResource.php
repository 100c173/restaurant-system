<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ManageSubscriptions;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Auth;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $recordTitleAttribute = 'id';
    protected static string|UnitEnum|null $navigationGroup = 'Subscriptions';
    protected static ?int $navigationSort = 3;


    // ── Shared status options ─────────────────────────────────────

    private static function statusOptions(): array
    {
        return [
            'trial' => 'Trial',
            'active' => 'Active',
            'past_due' => 'Past due',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ];
    }

    private static function statusColors(): array
    {
        return [
            'trial' => 'info',
            'active' => 'success',
            'past_due' => 'warning',
            'cancelled' => 'danger',
            'expired' => 'gray',
        ];
    }

    private static function intervalOptions(): array
    {
        return [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
            'lifetime' => 'Lifetime',
        ];
    }
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Subscription')
                    ->columns(2)
                    ->schema([

                        Select::make('tenant_id')
                            ->label('Tenant (restaurant)')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn(string $search) =>
                                Tenant::where('id', 'like', "%{$search}%")
                                    ->orWhere('data->name', 'like', "%{$search}%")
                                    ->limit(20)
                                    ->get()
                                    ->pluck('data.name', 'id')
                                    ->toArray()
                            )
                            ->getOptionLabelUsing(
                                fn($value) =>
                                Tenant::find($value)?->data['name'] ?? $value
                            ),

                        Select::make('plan_id')
                            ->label('Plan')
                            ->required()
                            ->searchable()
                            ->options(
                                Plan::active()->orderBy('price')->pluck('name', 'id')
                            )
                            ->live()
                            // Auto-fill price + interval when plan is chosen
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state)
                                    return;
                                $plan = Plan::find($state);
                                if (!$plan)
                                    return;
                                $set('price', $plan->price);
                                $set('billing_interval', $plan->billing_interval);
                            }),

                        TextInput::make('price')
                            ->label('Price (USD)')
                            ->prefix('$')
                            ->numeric()
                            ->readOnly()
                            ->helperText('Auto-filled from the selected plan.'),

                        Select::make('billing_interval')
                            ->label('Billing interval')
                            ->options(self::intervalOptions())
                            ->disabled()
                            ->helperText('Auto-filled from the selected plan.'),

                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->options(self::statusOptions())
                            ->default('trial'),

                        TextInput::make('payment_reference')
                            ->label('Payment reference')
                            ->maxLength(100)
                            ->placeholder('e.g. TXN-20240501-8823'),
                    ]),

                Section::make('Dates')
                    ->columns(2)
                    ->schema([

                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false),

                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->after('starts_at'),

                        DateTimePicker::make('trial_ends_at')
                            ->label('Trial ends at')
                            ->seconds(false),

                        DateTimePicker::make('cancelled_at')
                            ->label('Cancelled at')
                            ->seconds(false),
                    ]),

                Section::make('Notes')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Admin notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tenant.name')
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn(Subscription $r) => $r->tenant_id),

                TextColumn::make('plan.name')
                    ->label('Plan')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('SPY')
                    ->sortable(),

                TextColumn::make('billing_interval')
                    ->label('Interval')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'daily' => 'gray',
                        'weekly' => 'gray',
                        'monthly' => 'info',
                        'yearly' => 'success',
                        'lifetime' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state) => ucfirst($state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors(self::statusColors())
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'past_due' => 'Past due',
                        default => ucfirst($state),
                    })
                    ->sortable(),

                // Expiry / trial date — most operationally useful single date
                TextColumn::make('ends_at')
                    ->label('Ends at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->description(
                        fn(Subscription $r) =>
                        $r->status === 'trial' && $r->trial_ends_at
                        ? 'Trial ends ' . $r->trial_ends_at->format('d M Y')
                        : null
                    )
                    ->color(
                        fn(Subscription $r) =>
                        $r->ends_at?->isPast() ? 'danger' : null
                    ),

                TextColumn::make('starts_at')
                    ->label('Started')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('trial_ends_at')
                    ->label('Trial ends')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cancelled_at')
                    ->label('Cancelled')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('activatedBy.name')
                    ->label('Activated by')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // ── Filters ───────────────────────────────────────────

            ->filters([

                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
                    ->options(self::statusOptions()),

                SelectFilter::make('plan_id')
                    ->label('Plan')
                    ->searchable()
                    ->multiple()
                    ->options(Plan::active()->orderBy('price')->pluck('name', 'id')),

                SelectFilter::make('billing_interval')
                    ->label('Interval')
                    ->multiple()
                    ->options(self::intervalOptions()),

                Filter::make('expires_soon')
                    ->label('Expires within 7 days')
                    ->query(
                        fn($query) =>
                        $query->where('ends_at', '<=', now()->addDays(7))
                            ->where('ends_at', '>=', now())
                            ->where('status', 'active')
                    ),

                Filter::make('trial_expiring')
                    ->label('Trial expiring within 7 days')
                    ->query(
                        fn($query) =>
                        $query->where('trial_ends_at', '<=', now()->addDays(7))
                            ->where('trial_ends_at', '>=', now())
                            ->where('status', 'trial')
                    ),

                Filter::make('ends_at')
                    ->label('End date range')
                    ->form([
                        DatePicker::make('ends_from')->label('From'),
                        DatePicker::make('ends_until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['ends_from'], fn($q, $d) => $q->whereDate('ends_at', '>=', $d))
                            ->when($data['ends_until'], fn($q, $d) => $q->whereDate('ends_at', '<=', $d));
                    }),

            ], layout: FiltersLayout::AboveContent)

            ->filtersFormColumns(4)

            // ── Row actions ───────────────────────────────────────

            ->actions([

                // ① Activate
                Action::make('activate')
                    ->label('Activate')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn(Subscription $r) =>
                        in_array($r->status, ['trial', 'past_due', 'expired'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Activate subscription')
                    ->modalDescription('This will mark the subscription as active and set the activation timestamp.')
                    ->form([
                        DateTimePicker::make('starts_at')
                            ->label('Start date')
                            ->default(now())
                            ->required(),
                      

                        DateTimePicker::make('ends_at')
                            ->label('End date')
                            ->required()
                            ->default(now()->addMonth()),

                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        $record->update([
                            'status' => 'active',
                            'starts_at' => $data['starts_at'],
                            'ends_at' => $data['ends_at'],
                            'activated_by' => Auth::id(),
                            'activated_at' => now(),
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->title('Subscription activated')
                            ->success()
                            ->send();
                    }),

                // ② Change plan
                Action::make('change_plan')
                    ->label('Change plan')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('info')
                    ->visible(
                        fn(Subscription $r) =>
                        in_array($r->status, ['trial', 'active', 'past_due'])
                    )
                    ->modalHeading('Change plan')
                    ->form(fn(Subscription $record) => [
                        Select::make('plan_id')
                            ->label('New plan')
                            ->required()
                            ->searchable()
                            ->default($record->plan_id)
                            ->options(
                                Plan::active()->orderBy('price')->pluck('name', 'id')
                            )
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if (!$state)
                                    return;
                                $plan = Plan::find($state);
                                if (!$plan)
                                    return;
                                $set('price', $plan->price);
                                $set('billing_interval', $plan->billing_interval);
                            }),

                        TextInput::make('price')
                            ->label('Price')
                            ->prefix('$')
                            ->readOnly()
                            ->default($record->price),

                        Select::make('billing_interval')
                            ->label('Interval')
                            ->options(self::intervalOptions())
                            ->disabled()
                            ->default($record->billing_interval),

                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2)
                            ->default($record->notes),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        $plan = Plan::find($data['plan_id']);

                        $record->update([
                            'plan_id' => $plan->id,
                            'price' => $plan->price,
                            'billing_interval' => $plan->billing_interval,
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->title('Plan changed to ' . $plan->name)
                            ->success()
                            ->send();
                    }),

                // ③ Renew / extend
                Action::make('renew')
                    ->label('Renew')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(
                        fn(Subscription $r) =>
                        in_array($r->status, ['active', 'past_due', 'expired'])
                    )
                    ->modalHeading('Renew / extend subscription')
                    ->form(fn(Subscription $record) => [
                        DateTimePicker::make('ends_at')
                            ->label('New end date')
                            ->required()
                            ->seconds(false)
                            ->default(
                                $record->ends_at?->isFuture()
                                ? $record->ends_at
                                : now()->addMonth()
                            ),

                        Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->rows(2)
                            ->default($record->notes),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        $record->update([
                            'ends_at' => $data['ends_at'],
                            'status' => 'active',
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->title('Subscription renewed until ' . $data['ends_at'])
                            ->success()
                            ->send();
                    }),

                // ④ Expire manually
                Action::make('expire')
                    ->label('Expire')
                    ->icon('heroicon-o-clock')
                    ->color('gray')
                    ->visible(
                        fn(Subscription $r) =>
                        in_array($r->status, ['active', 'trial', 'past_due'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark as expired')
                    ->modalDescription('This will immediately set the status to Expired and set end date to now.')
                    ->action(function (Subscription $record): void {
                        $record->update([
                            'status' => 'expired',
                            'ends_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Subscription marked as expired')
                            ->warning()
                            ->send();
                    }),

                // ⑤ Cancel
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(
                        fn(Subscription $r) =>
                        in_array($r->status, ['trial', 'active', 'past_due'])
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Cancel subscription')
                    ->modalDescription('The subscription will be marked as cancelled immediately.')
                    ->form([
                        Textarea::make('notes')
                            ->label('Cancellation reason (optional)')
                            ->rows(2),
                    ])
                    ->action(function (Subscription $record, array $data): void {
                        $record->update([
                            'status' => 'cancelled',
                            'cancelled_at' => now(),
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);

                        Notification::make()
                            ->title('Subscription cancelled')
                            ->danger()
                            ->send();
                    }),

                // ⑥ Free edit
                EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit subscription'),

            ])

            // ── Bulk actions ──────────────────────────────────────

            ->bulkActions([
                BulkActionGroup::make([

                    BulkAction::make('bulk_activate')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(fn($r) => $r->update([
                                'status' => 'active',
                                'activated_by' => Auth::id(),
                                'activated_at' => now(),
                                'starts_at' => $r->starts_at ?? now(),
                            ]));

                            Notification::make()
                                ->title('Selected subscriptions activated')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('bulk_cancel')
                        ->label('Cancel selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records): void {
                            $records->each(fn($r) => $r->update([
                                'status' => 'cancelled',
                                'cancelled_at' => now(),
                            ]));

                            Notification::make()
                                ->title('Selected subscriptions cancelled')
                                ->danger()
                                ->send();
                        }),

                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('created_at', 'desc')
            ->striped()
            ->poll('60s'); // auto-refresh every 60s
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubscriptions::route('/'),
            'create' => Pages\CreateSubscription::route('/create'),
            'edit' => Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
