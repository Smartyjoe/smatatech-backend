<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'available',
        'used',
        'total',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'available' => 'integer',
            'used' => 'integer',
            'total' => 'integer',
            'expires_at' => 'datetime',
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
     * Transform credit data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'available' => $this->available,
            'used' => $this->used,
            'total' => $this->total,
            'expiresAt' => $this->expires_at?->toIso8601String(),
        ];
    }
}
