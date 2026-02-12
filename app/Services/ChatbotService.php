<?php

namespace App\Services;

use App\Models\ChatbotConfig;
use App\Models\ChatbotTraining;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotService
{
    public function getPublicConfig(): array
    {
        $config = ChatbotConfig::first();

        if (!$config) {
            return [
                'isEnabled' => true,
                'greetingMessage' => "Hello! I'm the Smatatech AI assistant. How can I help you today?",
                'suggestedQuestions' => [],
            ];
        }

        $suggestedQuestions = ChatbotTraining::query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->latest()
            ->limit(4)
            ->pluck('title')
            ->filter()
            ->values()
            ->all();

        return [
            'isEnabled' => (bool) $config->is_enabled,
            'greetingMessage' => $config->greeting_message ?: "Hello! I'm the Smatatech AI assistant. How can I help you today?",
            'suggestedQuestions' => $suggestedQuestions,
        ];
    }

    public function reply(string $message, array $history = []): string
    {
        $config = ChatbotConfig::first();
        if ($config && !$config->is_enabled) {
            return $config->fallback_message ?: 'The assistant is currently unavailable.';
        }

        $endpoint = config('services.ai_blog.text_endpoint', 'https://openrouter.ai/api/v1/chat/completions');
        $key = config('services.ai_blog.text_key');
        $model = config('services.ai_blog.chat_model', config('services.ai_blog.text_model', 'gpt-4o-mini'));

        if (!$endpoint || !$key) {
            Log::warning('Chatbot provider not configured.', [
                'endpoint_configured' => !empty($endpoint),
                'key_configured' => !empty($key),
            ]);
            return $config?->fallback_message ?: 'The assistant is not configured yet.';
        }

        $trainingItems = ChatbotTraining::query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->latest()
            ->limit(20)
            ->get(['title', 'content', 'category']);

        $knowledgeBase = $trainingItems
            ->map(fn ($item) => "[{$item->category}] Q: {$item->title}\nA: {$item->content}")
            ->implode("\n\n");

        $allowedTopics = is_array($config?->allowed_topics) ? implode(', ', $config->allowed_topics) : '';
        $restrictedTopics = is_array($config?->restricted_topics) ? implode(', ', $config->restricted_topics) : '';
        $tone = $config?->personality_tone ?: 'professional';

        $systemPrompt = trim((string) ($config?->system_prompt ?? ''));
        if ($systemPrompt === '') {
            $systemPrompt = 'You are the Smatatech assistant. Provide concise, accurate, practical answers.';
        }

        $policyPrompt = "Tone: {$tone}\n";
        if ($allowedTopics !== '') {
            $policyPrompt .= "Preferred topics: {$allowedTopics}\n";
        }
        if ($restrictedTopics !== '') {
            $policyPrompt .= "Restricted topics: {$restrictedTopics}. If asked, politely decline and suggest contacting support.\n";
        }
        if ($knowledgeBase !== '') {
            $policyPrompt .= "Use this internal knowledge first:\n{$knowledgeBase}\n";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt . "\n\n" . $policyPrompt],
        ];

        foreach (array_slice($history, -10) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $role = $item['role'] ?? null;
            $content = $item['content'] ?? '';
            if (!in_array($role, ['user', 'assistant'], true) || !is_string($content) || trim($content) === '') {
                continue;
            }
            $messages[] = ['role' => $role, 'content' => $content];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        $headers = ['Authorization' => "Bearer {$key}"];
        if (str_contains($endpoint, 'openrouter.ai')) {
            $headers['HTTP-Referer'] = config('app.url');
            $headers['X-Title'] = config('app.name');
        }

        try {
            $response = Http::timeout(60)
                ->withHeaders($headers)
                ->post($endpoint, [
                    'model' => $model,
                    'messages' => $messages,
                    'temperature' => 0.6,
                    'max_tokens' => 500,
                ]);
        } catch (\Throwable $e) {
            Log::error('Chatbot request failed.', [
                'endpoint' => $endpoint,
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            return $config?->fallback_message ?: 'I am having trouble right now. Please try again shortly.';
        }

        if (!$response->ok()) {
            Log::error('Chatbot provider returned error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return $config?->fallback_message ?: 'I am having trouble right now. Please try again shortly.';
        }

        $data = $response->json();
        $content = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($content) || trim($content) === '') {
            return $config?->fallback_message ?: 'I am unable to answer that right now.';
        }

        return trim($content);
    }
}

