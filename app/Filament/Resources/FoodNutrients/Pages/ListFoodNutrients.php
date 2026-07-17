<?php

namespace App\Filament\Resources\FoodNutrients\Pages;

use App\Filament\Resources\FoodNutrients\FoodNutrientResource;
use App\Services\FoodImportExportService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ListFoodNutrients extends ListRecords
{
    protected static string $resource = FoodNutrientResource::class;

    public function table(Table $table): Table
    {
        return FoodNutrientResource::table($table)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import XLSX')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('file')
                        ->label('Food nutrients spreadsheet (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports/food-nutrients')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->helperText('Must contain an "FDC ID" column to match each row to an existing food.'),
                ])
                ->action(function (array $data, FoodImportExportService $service): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $result = $service->importFoodNutrients($path);

                    Notification::make()
                        ->title('Food nutrients imported')
                        ->body(
                            "{$result['imported']} rows imported, {$result['skipped']} skipped"
                            .(count($result['errors']) ? ' (see logs for unmatched FDC IDs).' : '.')
                        )
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (FoodImportExportService $service) => $service->exportFoodNutrients()),

            CreateAction::make(),
        ];
    }
}
