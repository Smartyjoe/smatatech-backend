<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'greeting_message',
        'initial_message',
        'fallback_message',
        'personality_tone',
        'system_prompt',
        'allowed_topics',
        'restricted_topics',
        'is_enabled',
    ];

    protected $casts = [
        'allowed_topics' => 'array',
        'restricted_topics' => 'array',
        'is_enabled' => 'boolean',
    ];
}
