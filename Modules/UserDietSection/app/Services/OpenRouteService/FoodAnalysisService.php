<?php

namespace Modules\UserDietSection\Services\OpenRouteService;

class FoodAnalysisService extends BaseOpenRouterService
{
    public function analyze(string $imageUrl): array
    {
        $prompt = <<<TEXT
            You are a nutrition expert. Analyze this food image.
            Provide a brief description of the meal, then estimated calories, protein (g), fat (g), and carbohydrates (g) per typical serving.

            Output format EXACTLY like this:
            Description: ...
            Calories: ... kcal
            Protein: ... g
            Fat: ... g
            Carbs: ... g
        TEXT;

        $content = $this->sendRequest($prompt, $imageUrl);

        return [
            'description' => $this->extract($content, 'Description'),
            'calories' => $this->extract($content, 'Calories'),
            'protein' => $this->extract($content, 'Protein'),
            'fat' => $this->extract($content, 'Fat'),
            'carbs' => $this->extract($content, 'Carbs'),
        ];
    }

    private function extract(string $text, string $key): string
    {
        preg_match("/{$key}:\s*(.*)/i", $text, $matches);
        return isset($matches[1]) ? trim($matches[1]) : 'not found';
    }
}