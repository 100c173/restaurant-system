<?php

namespace Modules\UserDietSection\Services\OpenRouteService;

use Illuminate\Support\Facades\Http;

abstract class BaseOpenRouterService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $model;

    public function __construct()
    {
        $this->apiKey  = config('services.openrouter.api_key');
        $this->baseUrl = config('services.openrouter.base_url');
        $this->model   = config('services.openrouter.model');
    }

    protected function sendRequest(string $prompt, string $imageUrl): string
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->baseUrl, [
            'model'      => $this->model,
            'max_tokens' => 500,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text',      'text'      => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => $imageUrl]],
                ],
            ]],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenRouter API Error: ' . $response->body());
        }

        return $response->json('choices.0.message.content', '');
    }

    abstract public function analyze(string $imageUrl): array;
}