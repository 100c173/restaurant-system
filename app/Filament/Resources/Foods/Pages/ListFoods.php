<?php

namespace App\Filament\Resources\Foods\Pages;

use App\Filament\Resources\Foods\FoodResource;
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

class ListFoods extends ListRecords
{
    protected static string $resource = FoodResource::class;

    public function table(Table $table): Table
    {
        return FoodResource::table($table)
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
                        ->label('Foods spreadsheet (.xlsx)')
                        ->required()
                        ->disk('local')
                        ->directory('imports/foods')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                ])
                ->action(function (array $data, FoodImportExportService $service): void {
                    $path = Storage::disk('local')->path($data['file']);

                    $result = $service->importFoods($path);

                    Notification::make()
                        ->title('Foods imported')
                        ->body("{$result['imported']} rows imported, {$result['skipped']} skipped.")
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export XLSX')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (FoodImportExportService $service) => $service->exportFoods()),

            CreateAction::make(),
        ];
    }
}
