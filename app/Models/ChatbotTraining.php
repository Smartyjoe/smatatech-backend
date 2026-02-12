<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotTraining extends Model
{
    use HasFactory;

    protected $table = 'chatbot_training';

    protected $fillable = [
        'title',
        'content',
        'category',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];
}
