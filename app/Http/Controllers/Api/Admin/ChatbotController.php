<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\ChatbotConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends BaseApiController
{
    /**
     * Get chatbot config.
     * GET /admin/chatbot/config
     */
    public function getConfig(): JsonResponse
    {
        $config = ChatbotConfig::current();

        return $this->successResponse($config->toApiResponse());
    }

    /**
     * Update chatbot config.
     * PUT /admin/chatbot/config
     */
    public function updateConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'systemPrompt' => 'nullable|string',
            'personalityTone' => 'sometimes|string|in:professional,friendly,casual,formal,technical',
            'allowedTopics' => 'nullable|array',
            'allowedTopics.*' => 'string',
            'restrictedTopics' => 'nullable|array',
            'restrictedTopics.*' => 'string',
            'greetingMessage' => 'nullable|string',
            'fallbackMessage' => 'nullable|string',
            'isEnabled' => 'sometimes|boolean',
            'versionLabel' => 'nullable|string|max:50',
        ]);

        $config = ChatbotConfig::current();

        $updateData = [];
        if (array_key_exists('systemPrompt', $validated)) $updateData['system_prompt'] = $validated['systemPrompt'];
        if (isset($validated['personalityTone'])) $updateData['personality_tone'] = $validated['personalityTone'];
        if (array_key_exists('allowedTopics', $validated)) $updateData['allowed_topics'] = $validated['allowedTopics'];
        if (array_key_exists('restrictedTopics', $validated)) $updateData['restricted_topics'] = $validated['restrictedTopics'];
        if (array_key_exists('greetingMessage', $validated)) $updateData['greeting_message'] = $validated['greetingMessage'];
        if (array_key_exists('fallbackMessage', $validated)) $updateData['fallback_message'] = $validated['fallbackMessage'];
        if (isset($validated['isEnabled'])) $updateData['is_enabled'] = $validated['isEnabled'];
        if (array_key_exists('versionLabel', $validated)) $updateData['version_label'] = $validated['versionLabel'];

        $config->update($updateData);

        ActivityLog::log(
            'chatbot_config_updated',
            'Chatbot configuration updated',
            'Chatbot settings were updated',
            $request->user()
        );

        return $this->successResponse($config->fresh()->toApiResponse(), 'Chatbot configuration updated.');
    }

    /**
     * Toggle chatbot status.
     * POST /admin/chatbot/toggle
     */
    public function toggle(Request $request): JsonResponse
    {
        $config = ChatbotConfig::current();
        $config->update(['is_enabled' => !$config->is_enabled]);

        $status = $config->is_enabled ? 'enabled' : 'disabled';

        ActivityLog::log(
            'chatbot_toggled',
            "Chatbot {$status}",
            "Chatbot was {$status}",
            $request->user()
        );

        return $this->successResponse($config->fresh()->toApiResponse(), "Chatbot {$status}.");
    }
}
