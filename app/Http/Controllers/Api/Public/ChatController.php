<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ChatbotConfig;
use App\Models\ChatbotConversation;
use App\Models\SiteSetting;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ChatController extends BaseApiController
{
    /**
     * Get public chatbot configuration.
     * GET /chatbot/config
     */
    public function config(): JsonResponse
    {
        $config = Cache::remember('public_chatbot_config', 3600, function () {
            $chatbotConfig = ChatbotConfig::first();
            $settings = SiteSetting::getPublicSettings();
            $services = Service::published()->ordered()->pluck('title')->toArray();

            return [
                'isEnabled' => (bool) ($chatbotConfig?->is_enabled ?? false),
                'greetingMessage' => $chatbotConfig?->greeting_message ?? 'Hello! How can I help you today?',
                'suggestedQuestions' => $chatbotConfig?->allowed_topics ?? [
                    'What services do you offer?',
                    'How can I get a quote?',
                    'Tell me about your company',
                ],
                'companyInfo' => [
                    'name' => $settings['siteName'] ?? 'Smatatech',
                    'services' => $services,
                    'contactEmail' => $settings['contact']['email'] ?? $settings['contactEmail'] ?? null,
                    'contactPhone' => $settings['contact']['phone'] ?? $settings['contactPhone'] ?? null,
                ],
            ];
        });

        return $this->successResponse($config);
    }

    /**
     * Process chat message via server-side proxy.
     * POST /chat
     */
    public function chat(Request $request): JsonResponse
    {
        // Rate limiting
        $key = 'chat:' . ($request->ip() ?? 'unknown');
        $maxAttempts = (int) env('CHATBOT_RATE_LIMIT', 10);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->errorResponse(
                "Too many requests. Please wait {$seconds} seconds.",
                429
            );
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'conversationId' => 'nullable|string|max:36',
            'context' => 'nullable|array',
            'context.page' => 'nullable|string|max:500',
            'context.previousMessages' => 'nullable|array|max:10',
        ]);

        // Get or create conversation
        $conversationId = $validated['conversationId'] ?? Str::uuid()->toString();
        $conversation = ChatbotConversation::firstOrCreate(
            ['id' => $conversationId],
            [
                'session_id' => $request->session()->getId() ?? Str::random(40),
                'ip_address' => $request->ip(),
                'messages' => [],
            ]
        );

        // Get chatbot configuration
        $chatbotConfig = ChatbotConfig::first();
        $systemPrompt = $chatbotConfig?->system_prompt ?? $this->getDefaultSystemPrompt();

        // Build messages array for AI
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add previous messages from context
        $previousMessages = $validated['context']['previousMessages'] ?? [];
        foreach ($previousMessages as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = [
                    'role' => $msg['role'],
                    'content' => $msg['content'],
                ];
            }
        }

        // Add current message
        $messages[] = ['role' => 'user', 'content' => $validated['message']];

        // Call OpenRouter API
        $apiKey = env('OPENROUTER_API_KEY');
        if (!$apiKey) {
            return $this->errorResponse('Chat service is not configured.', 503);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'HTTP-Referer' => env('APP_URL'),
                'X-Title' => env('APP_NAME', 'Smatatech'),
            ])->timeout(30)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => env('OPENROUTER_MODEL', 'qwen/qwen3-4b:free'),
                'messages' => $messages,
                'max_tokens' => (int) env('CHATBOT_MAX_TOKENS', 500),
                'temperature' => (float) env('CHATBOT_TEMPERATURE', 0.7),
            ]);

            if (!$response->successful()) {
                \Log::error('OpenRouter API error', ['response' => $response->body()]);
                return $this->errorResponse('Failed to get response from AI service.', 502);
            }

            $data = $response->json();
            $aiMessage = $data['choices'][0]['message']['content'] ?? 'I apologize, but I could not generate a response.';

            // Save messages to conversation
            $conversation->addMessage('user', $validated['message']);
            $conversation->addMessage('assistant', $aiMessage);

            return $this->successResponse([
                'message' => $aiMessage,
                'conversationId' => $conversationId,
            ]);

        } catch (\Exception $e) {
            \Log::error('Chat error: ' . $e->getMessage());
            return $this->errorResponse('An error occurred while processing your request.', 500);
        }
    }

    /**
     * Get default system prompt.
     */
    private function getDefaultSystemPrompt(): string
    {
        $settings = SiteSetting::getPublicSettings();
        $siteName = $settings['siteName'] ?? 'Smatatech';
        $services = Service::published()->pluck('title')->implode(', ');

        return "You are a helpful assistant for {$siteName}, a technology company specializing in: {$services}. " .
            "Be professional, friendly, and concise. Help visitors learn about services, answer questions, " .
            "and guide them to contact the team for detailed inquiries. " .
            "If asked about pricing or specific project details, encourage them to fill out the contact form or reach out directly.";
    }
}
