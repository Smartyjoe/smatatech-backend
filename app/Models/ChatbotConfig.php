<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ChatbotConfig extends Model
{
    use HasUuid;

    protected $table = 'chatbot_config';

    protected $fillable = [
        'system_prompt',
        'personality_tone',
        'allowed_topics',
        'restricted_topics',
        'greeting_message',
        'fallback_message',
        'is_enabled',
        'version_label',
    ];

    protected function casts(): array
    {
        return [
            'allowed_topics' => 'array',
            'restricted_topics' => 'array',
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * Get the current config (singleton pattern).
     */
    public static function current(): self
    {
        return self::first() ?? self::create([
            'system_prompt' => 'You are a helpful assistant for Smatatech Technologies.',
            'personality_tone' => 'professional',
            'greeting_message' => 'Hello! How can I help you today?',
            'fallback_message' => 'I\'m sorry, I don\'t understand. Can you please rephrase?',
            'is_enabled' => true,
        ]);
    }

    /**
     * Transform config data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'systemPrompt' => $this->system_prompt,
            'personalityTone' => $this->personality_tone,
            'allowedTopics' => $this->allowed_topics,
            'restrictedTopics' => $this->restricted_topics,
            'greetingMessage' => $this->greeting_message,
            'fallbackMessage' => $this->fallback_message,
            'isEnabled' => $this->is_enabled,
            'versionLabel' => $this->version_label,
        ];
    }
}
