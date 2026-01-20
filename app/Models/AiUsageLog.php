<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AiUsageLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'ai_tool_id',
        'input',
        'output',
        'credits_used',
        'status',
        'execution_time_ms',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'credits_used' => 'integer',
            'execution_time_ms' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AI tool.
     */
    public function aiTool()
    {
        return $this->belongsTo(AiTool::class);
    }

    /**
     * Transform usage log data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'tool' => $this->aiTool?->toApiResponse(),
            'input' => $this->input,
            'output' => $this->output,
            'creditsUsed' => $this->credits_used,
            'status' => $this->status,
            'executionTimeMs' => $this->execution_time_ms,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
