<?php
namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use App\Models\Feature;
use App\Models\Plan;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewFeaturePlans extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = FeatureResource::class;
    protected  string $view     = 'filament.pages.view-feature-plans';

    public Feature $record;

    public function getTitle(): string
    {
        return "Plans — {$this->record->name}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            FeatureResource::getUrl()                                              => 'Features',
            FeatureResource::getUrl('view-plans', ['record' => $this->record])     => $this->record->name,
            '#'                                                                    => 'Plans',
        ];
    }

    // ── Header actions ────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('assign_to_plan')
                ->label('Assign to plan')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading("Assign to plan — {$this->record->name}")
                ->form(function () {
                    $assigned = $this->record->plans()->pluck('plans.id');

                    return [
                        Forms\Components\Select::make('plan_id')
                            ->label('Plan')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) use ($assigned) {
                                return Plan::whereNotIn('id', $assigned)
                                           ->where('is_active', true)
                                           ->where(fn ($q) => $q
                                               ->where('name', 'like', "%{$search}%")
                                               ->orWhere('code', 'like', "%{$search}%")
                                           )
                                           ->orderBy('price')
                                           ->limit(20)
                                           ->pluck('name', 'id');
                            })
                            ->getOptionLabelUsing(fn ($value) => Plan::find($value)?->name)
                            ->helperText('Search by name or code. Only plans without this feature are shown.'),

                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->helperText(match($this->record->type) {
                                'boolean' => 'Enter: true  or  false',
                                'limit'   => 'Enter a number, or -1 for unlimited.',
                                default   => 'Enter any string.',
                            }),
                    ];
                })
                ->action(function (array $data): void {
                    $this->record->plans()->attach($data['plan_id'], [
                        'value' => $data['value'],
                    ]);

                    Notification::make()
                        ->title('Feature assigned to plan')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('back')
                ->label('Back to features')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(FeatureResource::getUrl()),
        ];
    }

    // ── Table ─────────────────────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Plan::query()
                    ->whereHas('features', fn (Builder $q) =>
                        $q->where('features.id', $this->record->id)
                    )
                    ->with(['features' => fn ($q) =>
                        $q->where('features.id', $this->record->id)
                    ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_interval')
                    ->label('Interval')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'monthly'  => 'info',
                        'yearly'   => 'success',
                        'lifetime' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                Tables\Columns\TextColumn::make('pivot_value')
                    ->label('Value')
                    ->badge()
                    ->getStateUsing(function (Plan $record): string {
                        $pivot = $record->features->first()?->pivot;
                        $value = $pivot?->value ?? '—';
                        $type  = $this->record->type;

                        return match(true) {
                            $type === 'boolean' && $value === 'true'  => 'Enabled',
                            $type === 'boolean' && $value === 'false' => 'Disabled',
                            $type === 'limit'   && $value === '-1'    => 'Unlimited',
                            default                                   => $value,
                        };
                    })
                    ->color(function (Plan $record): string {
                        $pivot = $record->features->first()?->pivot;
                        $value = $pivot?->value ?? '';
                        $type  = $this->record->type;

                        return match(true) {
                            $type === 'boolean' && $value === 'true'  => 'success',
                            $type === 'boolean' && $value === 'false' => 'danger',
                            $type === 'limit'   && $value === '-1'    => 'primary',
                            $type === 'limit'                         => 'info',
                            default                                   => 'gray',
                        };
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit_value')
                    ->label('Edit value')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading('Edit value for this plan')
                    ->form(fn (Plan $record) => [
                        Forms\Components\TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->default(
                                $record->features
                                    ->firstWhere('id', $this->record->id)
                                    ?->pivot->value
                            )
                            ->helperText(match($this->record->type) {
                                'boolean' => 'Enter: true  or  false',
                                'limit'   => 'Enter a number, or -1 for unlimited.',
                                default   => 'Enter any string.',
                            }),
                    ])
                    ->action(function (Plan $record, array $data): void {
                        $this->record->plans()->updateExistingPivot($record->id, [
                            'value' => $data['value'],
                        ]);

                        Notification::make()
                            ->title('Value updated')
                            ->success()
                            ->send();
                    }),

                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove from plan')
                    ->modalDescription('This only removes the assignment. The feature and plan themselves are not deleted.')
                    ->action(function (Plan $record): void {
                        $this->record->plans()->detach($record->id);

                        Notification::make()
                            ->title('Removed from plan')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('Not assigned to any plan')
            ->emptyStateDescription('Use "Assign to plan" above to link this feature to a plan.')
            ->emptyStateIcon('heroicon-o-rectangle-stack')
            ->striped();
    }
}