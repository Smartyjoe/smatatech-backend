<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ServiceInquiry extends Model
{
    use HasUuid;

    protected $fillable = [
        'service_id',
        'service_slug',
        'name',
        'email',
        'phone',
        'company',
        'budget_range',
        'timeline',
        'message',
        'status',
        'ip_address',
    ];

    /**
     * Get the service associated with this inquiry.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope for new inquiries.
     */
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    /**
     * Transform inquiry data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'serviceId' => $this->service_id,
            'serviceSlug' => $this->service_slug,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'budgetRange' => $this->budget_range,
            'timeline' => $this->timeline,
            'message' => $this->message,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
