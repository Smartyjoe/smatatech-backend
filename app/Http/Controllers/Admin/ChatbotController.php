<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotConfig;
use App\Models\ChatbotTraining;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function getConfig()
    {
        $config = ChatbotConfig::first();

        if (!$config) {
            // Create default config
            $config = ChatbotConfig::create([
                'name' => 'AI Assistant',
                'greeting_message' => 'Hello! How can I help you today?',
                'initial_message' => 'I\'m here to answer your questions about our services.',
                'fallback_message' => 'I\'m sorry, I don\'t have information about that. Please contact us directly.',
                'personality_tone' => 'professional',
                'is_enabled' => true,
                'allowed_topics' => [],
                'restricted_topics' => [],
                'system_prompt' => '',
            ]);
        }

        // Format response to match frontend expectations
        $formattedConfig = [
            'systemPrompt' => $config->system_prompt ?? '',
            'personalityTone' => $config->personality_tone ?? 'professional',
            'allowedTopics' => $config->allowed_topics ?? [],
            'restrictedTopics' => $config->restricted_topics ?? [],
            'greetingMessage' => $config->greeting_message ?? '',
            'fallbackMessage' => $config->fallback_message ?? '',
            'isEnabled' => $config->is_enabled ?? true,
            'versionLabel' => '', // Optional field
        ];

        return $this->successResponse($formattedConfig);
    }

    public function updateConfig(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'greetingMessage' => 'nullable|string',
            'initialMessage' => 'nullable|string',
            'fallbackMessage' => 'nullable|string',
            'personalityTone' => 'nullable|in:professional,friendly,casual',
            'systemPrompt' => 'nullable|string',
            'allowedTopics' => 'nullable|array',
            'restrictedTopics' => 'nullable|array',
            'isEnabled' => 'nullable|boolean',
        ]);

        // Map camelCase to snake_case
        $dbData = [
            'name' => $validated['name'] ?? null,
            'greeting_message' => $validated['greetingMessage'] ?? null,
            'initial_message' => $validated['initialMessage'] ?? null,
            'fallback_message' => $validated['fallbackMessage'] ?? null,
            'personality_tone' => $validated['personalityTone'] ?? null,
            'system_prompt' => $validated['systemPrompt'] ?? null,
            'allowed_topics' => $validated['allowedTopics'] ?? null,
            'restricted_topics' => $validated['restrictedTopics'] ?? null,
            'is_enabled' => $validated['isEnabled'] ?? null,
        ];

        // Remove null values
        $dbData = array_filter($dbData, fn($value) => $value !== null);

        $config = ChatbotConfig::first();

        if (!$config) {
            $config = ChatbotConfig::create($dbData);
        } else {
            $config->update($dbData);
        }

        // Return formatted response
        $formattedConfig = [
            'systemPrompt' => $config->system_prompt ?? '',
            'personalityTone' => $config->personality_tone ?? 'professional',
            'allowedTopics' => $config->allowed_topics ?? [],
            'restrictedTopics' => $config->restricted_topics ?? [],
            'greetingMessage' => $config->greeting_message ?? '',
            'fallbackMessage' => $config->fallback_message ?? '',
            'isEnabled' => $config->is_enabled ?? true,
            'versionLabel' => '',
        ];

        return $this->successResponse($formattedConfig, 'Chatbot configuration updated successfully');
    }

    public function toggle(Request $request)
    {
        $config = ChatbotConfig::first();

        if (!$config) {
            return $this->errorResponse('Chatbot configuration not found', 404);
        }

        $config->update(['is_enabled' => !$config->is_enabled]);

        return $this->successResponse([
            'is_enabled' => $config->is_enabled
        ], 'Chatbot toggled successfully');
    }

    public function getTraining(Request $request)
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $perPage = $perPage > 0 ? min($perPage, 100) : 15;
            $category = $request->input('category');
            $isActive = $request->input('is_active');
            $search = $request->input('search');

            $query = ChatbotTraining::query()->orderBy('priority', 'desc')->latest();

            if ($category) {
                $query->where('category', $category);
            }

            if ($isActive !== null) {
                $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('content', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            $training = $query->paginate($perPage);
            return $this->paginatedResponse($training);
        } catch (\Throwable $e) {
            Log::error('Failed to load chatbot training.', [
                'error' => $e->getMessage(),
            ]);
            return $this->errorResponse('Unable to load chatbot training right now.', 500);
        }
    }

    public function addTraining(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $training = ChatbotTraining::create($validated);

        return $this->successResponse($training, 'Training content added successfully', 201);
    }

    public function updateTraining(Request $request, $id)
    {
        $training = ChatbotTraining::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'category' => 'nullable|string',
            'priority' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $training->update($validated);

        return $this->successResponse($training, 'Training content updated successfully');
    }

    public function deleteTraining($id)
    {
        $training = ChatbotTraining::findOrFail($id);
        $training->delete();

        return $this->successResponse(null, 'Training content deleted successfully');
    }
}
