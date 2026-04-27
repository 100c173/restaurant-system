<?php

namespace Modules\UserDietSection\Services\OpenRouteService;

class NutritionLabelService extends BaseOpenRouterService
{
    public function analyze(string $imageUrl): array
    {
        $content = $this->sendRequest($this->getPrompt(), $imageUrl);
        return $this->parse($content);
    }

    private function getPrompt(): string
    {
        return <<<TEXT
            You are a nutrition label expert.
            Extract nutrition facts from the image.

            IMPORTANT RULES:
            - If the image is blurry, cropped, or unreadable, respond EXACTLY:
            Image is unclear or not readable. Please upload a clearer nutrition label.
            - Do NOT guess any values.
            - Use "not found" if a value is missing.

            Output format EXACTLY like this:
            Reference amount: ...
            Calories: ... kcal
            Protein: ... g
            Fat: ... g
            Carbs: ... g
        TEXT;
    }

    private function parse(string $text): array
    {
        $text = trim($text);

        if (str_contains($text, 'Image is unclear')) {
            return ['error' => 'image_not_clear'];
        }

        return [
            'reference_amount' => $this->extract($text, 'Reference amount'),
            'calories'         => $this->extract($text, 'Calories'),
            'protein'          => $this->extract($text, 'Protein'),
            'fat'              => $this->extract($text, 'Fat'),
            'carbs'            => $this->extract($text, 'Carbs'),
        ];
    }

    private function extract(string $text, string $key): string
    {
        preg_match("/{$key}:\s*(.*)/i", $text, $matches);
        return isset($matches[1]) ? trim($matches[1]) : 'not found';
    }
}