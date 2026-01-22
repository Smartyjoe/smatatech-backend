<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'company',
        'phone',
        'project_type',
        'budget',
        'services',
        'message',
        'status',
        'source_url',
        'referrer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
            'budget' => 'decimal:2',
        ];
    }

    /**
     * Scope for unread contacts.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }

    /**
     * Transform contact data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'phone' => $this->phone,
            'projectType' => $this->project_type,
            'budget' => $this->budget ? (float) $this->budget : null,
            'services' => $this->services ?? [],
            'message' => $this->message,
            'status' => $this->status,
            'metadata' => [
                'sourceUrl' => $this->source_url,
                'referrer' => $this->referrer,
                'utmSource' => $this->utm_source,
                'utmMedium' => $this->utm_medium,
                'utmCampaign' => $this->utm_campaign,
                'ipAddress' => $this->ip_address,
            ],
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
