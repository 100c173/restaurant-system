<?php

namespace App\Filament\Resources\FoodPortions\Pages;

use App\Filament\Resources\FoodPortions\FoodPortionResource;
use App\Services\FoodImportExportService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class ListFoodPortions extends ListRecords
{
    protected static string $resource = FoodPortionResource::class;

    public function table(Table $table): Table
    {
        return FoodPortionResource::table($table)
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
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
                        ->label('Food portions spreadsheet (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports/food-portions')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                ])
                ->action(function (array $data, FoodImportExportService $service): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $result = $service->importFoodPortions($path);

                    Notification::make()
                        ->title('Food portions imported')
                        ->body("{$result['imported']} rows imported, {$result['skipped']} skipped.")
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (FoodImportExportService $service) => $service->exportFoodPortions()),

            CreateAction::make(),
        ];
    }
}