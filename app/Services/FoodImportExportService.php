<?php

namespace App\Services;

use App\Exports\FoodNutrientsExport;
use App\Exports\FoodPortionsExport;
use App\Exports\FoodsExport;
use App\Exports\MeasureUnitsExport;
use App\Imports\FoodNutrientsImport;
use App\Imports\FoodPortionsImport;
use App\Imports\FoodsImport;
use App\Imports\MeasureUnitsImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Single place that knows how to import/export Foods and FoodNutrients
 * as XLSX. Both the Filament resource actions and the HTTP controller
 * call into this service, so the import/export logic only exists once.
 */
class FoodImportExportService
{
    public function exportFoods(string $filename = 'foods.xlsx'): BinaryFileResponse
    {
        return Excel::download(new FoodsExport, $filename);
    }

    public function exportFoodNutrients(string $filename = 'food-nutrients.xlsx'): BinaryFileResponse
    {
        return Excel::download(new FoodNutrientsExport, $filename);
    }

    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function importFoods(string $filePath): array
    {
        $import = new FoodsImport;

        Excel::import($import, $filePath);

        return [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ];
    }

    /**
     * @return array{imported: int, skipped: int, errors: array<int, string>}
     */
    public function importFoodNutrients(string $filePath): array
    {
        $import = new FoodNutrientsImport;

        Excel::import($import, $filePath);


        return [
            'imported' => $import->imported,
            'skipped' => $import->skipped,
            'errors' => $import->errors,
        ];
    }
    public function importMeasureUnits(string $path): array
    {
        $import = new MeasureUnitsImport();
        Excel::import($import, $path);

        return ['imported' => $import->imported, 'skipped' => $import->skipped, 'errors' => $import->errors];
    }

    public function exportMeasureUnits()
    {
        return Excel::download(new MeasureUnitsExport(), 'measure_units.xlsx');
    }

    public function importFoodPortions(string $path): array
    {
        $import = new FoodPortionsImport();
        Excel::import($import, $path);

        return ['imported' => $import->imported, 'skipped' => $import->skipped, 'errors' => $import->errors];
    }

    public function exportFoodPortions()
    {
        return Excel::download(new FoodPortionsExport(), 'food_portions.xlsx');
    }
}
