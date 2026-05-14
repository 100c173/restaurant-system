<?php

namespace Modules\UserDietSection\Services;

use App\Services\CloudinaryUploadService;
use Illuminate\Http\UploadedFile;
use Modules\UserDietSection\Services\OpenRouteService\BaseOpenRouterService;
use Modules\UserDietSection\Services\OpenRouteService\FoodAnalysisService;
use Modules\UserDietSection\Services\OpenRouteService\NutritionLabelService;
class ImageAnalysisOrchestratorService
{
    public function __construct(
        private readonly CloudinaryUploadService $cloudinary,
        private readonly FoodAnalysisService $foodService,
        private readonly NutritionLabelService $labelService,
    ) {
    }

    public function analyze(UploadedFile $file, $description , string $type): array
    {
        $analyzer = match ($type) {
            'meal' => $this->foodService,
            'tabel' => $this->labelService,
        };

        $imageUrl = $this->cloudinary->upload($file->getRealPath(), 'temp-analysis');

        try {
            return $analyzer->analyze($imageUrl, $description);
        } finally {
            // Always delete — even if analysis throws
            $this->cloudinary->delete($imageUrl);
        }
    }
}
