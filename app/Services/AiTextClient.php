<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiTextClient
{
    public function generate(string $prompt): string
    {
        $endpoint = config('services.ai_blog.text_endpoint');
        $key = config('services.ai_blog.text_key');
        $model = config('services.ai_blog.text_model', 'gpt-4o-mini');

        if ($endpoint) {
            $headers = $key ? ['Authorization' => "Bearer {$key}"] : [];
            if (str_contains($endpoint, 'openrouter.ai')) {
                $headers['HTTP-Referer'] = config('app.url');
                $headers['X-Title'] = config('app.name');
            }

            try {
                $response = Http::timeout(120)
                    ->withHeaders($headers)
                    ->post($endpoint, [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a professional blog writer. Respond only with valid JSON.',
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'temperature' => 0.7,
                    ]);
            } catch (\Throwable $e) {
                Log::error('AI text request failed.', [
                    'endpoint' => $endpoint,
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            if (!$response->ok()) {
                Log::error('AI text provider error response.', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('AI text provider error: ' . $response->body());
            }

            $data = $response->json();
            if (is_string($data)) {
                return $data;
            }

            if (isset($data['content'])) {
                return (string) $data['content'];
            }

            if (isset($data['text'])) {
                return (string) $data['text'];
            }

            if (isset($data['choices'][0]['message']['content'])) {
                return (string) $data['choices'][0]['message']['content'];
            }

            throw new \RuntimeException('AI text provider returned an unexpected response.');
        }

        $openAiKey = env('OPENAI_API_KEY');
        if ($openAiKey) {
            try {
                $response = Http::timeout(120)
                    ->withToken($openAiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'You are a professional blog writer. Respond only with valid JSON.',
                            ],
                            [
                                'role' => 'user',
                                'content' => $prompt,
                            ],
                        ],
                        'temperature' => 0.7,
                    ]);
            } catch (\Throwable $e) {
                Log::error('OpenAI fallback request failed.', [
                    'endpoint' => 'https://api.openai.com/v1/chat/completions',
                    'model' => $model,
                    'error' => $e->getMessage(),
                ]);
                throw $e;
            }

            if (!$response->ok()) {
                Log::error('OpenAI error response.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new \RuntimeException('OpenAI error: ' . $response->body());
            }

            $data = $response->json();
            return (string) ($data['choices'][0]['message']['content'] ?? '');
        }

        throw new \RuntimeException('AI text provider not configured.');
    }
}
