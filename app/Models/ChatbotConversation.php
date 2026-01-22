<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
    use HasUuid;

    protected $fillable = [
        'session_id',
        'user_id',
        'ip_address',
        'messages',
    ];

    protected function casts(): array
    {
        return [
            'messages' => 'array',
        ];
    }

    /**
     * Add a message to the conversation.
     */
    public function addMessage(string $role, string $content): self
    {
        $messages = $this->messages ?? [];
        $messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->messages = $messages;
        $this->save();
        return $this;
    }

    /**
     * Get the user that owns this conversation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
