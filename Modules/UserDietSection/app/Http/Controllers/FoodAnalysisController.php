<?php

namespace Modules\UserDietSection\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\UserDietSection\Http\Requests\AnalyzeMealRequest;
use Modules\UserDietSection\Services\ImageAnalysisOrchestratorService;
use Modules\UserDietSection\Services\OpenRouteService\FoodAnalysisService;
use Modules\UserDietSection\Services\OpenRouteService\NutritionLabelService;

class FoodAnalysisController extends Controller
{
    public function __construct(
        private readonly ImageAnalysisOrchestratorService $orchestrator,
    ) {
    }
    public function scan(AnalyzeMealRequest $request, string $type): JsonResponse
    {
        $result = $this->orchestrator->analyze($request->file('image'), $type);

        return $this->success($result);
    }
}
