<?php
namespace App\Filament\Resources\Plans\Pages;


use App\Filament\Resources\Plans\PlanResource;
use App\Models\Feature;
use App\Models\Plan;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ViewPlanFeatures extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = PlanResource::class;
    protected string $view     = 'filament.pages.view-plan-features';

    // Resolved from the route {record}
    public Plan $record;

    public function getTitle(): string
    {
        return "Features — {$this->record->name}";
    }

    public function getBreadcrumbs(): array
    {
        return [
            PlanResource::getUrl()                                              => 'Plans',
            PlanResource::getUrl('view-features', ['record' => $this->record]) => $this->record->name,
            '#'                                                                 => 'Features',
        ];
    }

    // ── Header actions ────────────────────────────────────────────────────

    protected function getHeaderActions(): array
    {
        return [
            Action::make('assign_feature')
                ->label('Assign feature')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->modalHeading("Assign feature — {$this->record->name}")
                ->form(function () {
                    $assigned = $this->record->features()->pluck('features.id');

                    return [
                        Select::make('feature_id')
                            ->label('Feature')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) use ($assigned) {
                                return Feature::whereNotIn('id', $assigned)
                                              ->where(fn ($q) => $q
                                                  ->where('name', 'like', "%{$search}%")
                                                  ->orWhere('code', 'like', "%{$search}%")
                                              )
                                              ->orderBy('name')
                                              ->limit(20)
                                              ->pluck('name', 'id');
                            })
                            ->getOptionLabelUsing(fn ($value) => Feature::find($value)?->name)
                            ->helperText('Search by name or code. Only unassigned features are shown.'),

                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->helperText('Boolean: true / false — Limit: number or -1 for unlimited — Text: any string.'),
                    ];
                })
                ->action(function (array $data): void {
                    $this->record->features()->attach($data['feature_id'], [
                        'value' => $data['value'],
                    ]);

                    Notification::make()
                        ->title('Feature assigned')
                        ->success()
                        ->send();
                }),

            Action::make('back')
                ->label('Back to plans')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(PlanResource::getUrl()),
        ];
    }

    // ── Table (pivot features) ────────────────────────────────────────────

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Feature::query()
                       ->whereHas('plans', fn (Builder $q) =>
                           $q->where('plans.id', $this->record->id)
                       )
                       ->with(['plans' => fn ($q) =>
                           $q->where('plans.id', $this->record->id)
                       ])
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Feature')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(fn (Feature $record) => $record->description),

                TextColumn::make('code')
                    ->label('Code')
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono')
                    ->searchable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'boolean' => 'warning',
                        'limit'   => 'info',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => ucfirst($state)),

                // Read the pivot value through the eager-loaded relation
                TextColumn::make('pivot_value')
                    ->label('Value')
                    ->badge()
                    ->getStateUsing(function (Feature $record): string {
                        $pivot = $record->plans->first()?->pivot;
                        $value = $pivot?->value ?? '—';
                        $type  = $record->type;

                        return match(true) {
                            $type === 'boolean' && $value === 'true'  => 'Enabled',
                            $type === 'boolean' && $value === 'false' => 'Disabled',
                            $type === 'limit'   && $value === '-1'    => 'Unlimited',
                            default                                   => $value,
                        };
                    })
                    ->color(function (Feature $record): string {
                        $pivot = $record->plans->first()?->pivot;
                        $value = $pivot?->value ?? '';
                        $type  = $record->type;

                        return match(true) {
                            $type === 'boolean' && $value === 'true'  => 'success',
                            $type === 'boolean' && $value === 'false' => 'danger',
                            $type === 'limit'   && $value === '-1'    => 'primary',
                            $type === 'limit'                         => 'info',
                            default                                   => 'gray',
                        };
                    }),
            ])
            ->headerActions([])
            ->actions([
                // Edit the pivot value inline
                Action::make('edit_value')
                    ->label('Edit value')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->modalHeading('Edit feature value')
                    ->form(fn (Feature $record) => [
                        TextInput::make('value')
                            ->label('Value')
                            ->required()
                            ->default(
                                $record->plans
                                    ->firstWhere('id', $this->record->id)
                                    ?->pivot->value
                            )
                            ->helperText(match($record->type) {
                                'boolean' => 'Enter: true  or  false',
                                'limit'   => 'Enter a number, or -1 for unlimited.',
                                default   => 'Enter any string.',
                            }),
                    ])
                    ->action(function (Feature $record, array $data): void {
                        $this->record->features()->updateExistingPivot($record->id, [
                            'value' => $data['value'],
                        ]);

                        Notification::make()
                            ->title('Value updated')
                            ->success()
                            ->send();
                    }),

                // Detach from this plan
                Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove feature from plan')
                    ->modalDescription('This only removes the assignment. The feature itself is not deleted.')
                    ->action(function (Feature $record): void {
                        $this->record->features()->detach($record->id);

                        Notification::make()
                            ->title('Feature removed from plan')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No features assigned')
            ->emptyStateDescription('Use "Assign feature" above to add features to this plan.')
            ->emptyStateIcon('heroicon-o-puzzle-piece')
            ->striped();
    }
}