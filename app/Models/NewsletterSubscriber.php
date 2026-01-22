<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class NewsletterSubscriber extends Model
{
    use HasUuid;

    protected $fillable = [
        'email',
        'consent',
        'status',
        'ip_address',
        'subscribed_at',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'consent' => 'boolean',
            'subscribed_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
        ];
    }

    /**
     * Scope for active subscribers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Transform subscriber data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'status' => $this->status,
            'subscribedAt' => $this->subscribed_at?->toIso8601String(),
        ];
    }
}
