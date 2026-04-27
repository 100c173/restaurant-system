<?php

namespace Modules\UserDietSection\Services;

use App\Services\CloudinaryUploadService;
use Illuminate\Http\UploadedFile;
use Modules\UserDietSection\Services\OpenRouteService\BaseOpenRouterService;

class ImageAnalysisOrchestratorService
{
    public function __construct(
        private readonly CloudinaryUploadService $cloudinary,
    ) {
    }

    public function analyze(UploadedFile $file, BaseOpenRouterService $analyzer): array
    {
        $imageUrl = $this->cloudinary->upload($file->getRealPath(), 'temp-analysis');

        try {
            return $analyzer->analyze($imageUrl);
        } finally {
            // Always delete — even if analysis throws
            $this->cloudinary->delete($imageUrl);
        }
    }
}
