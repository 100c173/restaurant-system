<?php

namespace Modules\UserDietSection\Services\OpenRouteService;

class FoodAnalysisService extends BaseOpenRouterService
{
    public function analyze(string $imageUrl): array
    {
        $prompt = $this->buildPrompt();

        $response = $this->sendRequest($prompt, $imageUrl);

        return $this->parseResponse($response);
    }

    private function buildPrompt(): string
    {
        return <<<TEXT
            You are a professional nutrition expert and food recognition AI.

            Analyze the provided food image and estimate nutritional values for ONE standard serving only.

            STRICT RULES:
            - Return ONLY valid JSON.
            - No markdown, no explanations, no extra text.
            - Never use ranges.
            - Never use words like "around", "approximately", "about", or "typically".
            - If uncertain, return the MOST REALISTIC AVERAGE estimate.
            - All numeric values must be integers.
            - All keys must be lowercase exactly as defined.
            - Dish name must contain 1 to 5 words only.
            - Description must be short and clear (maximum 15 words).
            - Confidence must be one of: "high", "medium", "low"

            ERROR HANDLING:
            {"error":"not_food"}
            {"error":"unclear_image"}
            {"error":"cannot_analyze_image"}

            VALID OUTPUT FORMAT:
            {
            "dish_name": "chicken burger",
            "description": "grilled chicken burger with lettuce and cheese",
            "calories": 420,
            "protein": 24,
            "carbs": 35,
            "fat": 18,
            "confidence": "high"
            }
        TEXT;
    }

    private function parseResponse(string $response): array
    {
        $data = json_decode($response, true);

        // JSON invalid
        if (!is_array($data)) {
            return $this->error('invalid_json');
        }

        // API returned error
        if (isset($data['error'])) {
            return $this->error($data['error']);
        }

        // Validate required fields
        $required = ['dish_name', 'description', 'calories', 'protein', 'carbs', 'fat', 'confidence'];

        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                return $this->error('missing_fields');
            }
        }

        // Type safety (best practice)
        return [
            'dish_name' => (string) $data['dish_name'],
            'description' => (string) $data['description'],
            'calories' => (int) $data['calories'],
            'protein' => (int) $data['protein'],
            'carbs' => (int) $data['carbs'],
            'fat' => (int) $data['fat'],
            'confidence' => (string) $data['confidence'],
        ];
    }

    private function error(string $type): array
    {
        return [
            'error' => $type
        ];
    }
}