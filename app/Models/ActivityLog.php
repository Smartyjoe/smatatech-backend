<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasUuid;

    const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'title',
        'description',
        'actor_type',
        'actor_id',
        'subject_type',
        'subject_id',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the actor (Admin or User).
     */
    public function actor()
    {
        return $this->morphTo();
    }

    /**
     * Get the subject (the affected model).
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Log an activity.
     * This method is fail-safe - it will never throw an exception.
     */
    public static function log(
        string $type,
        string $title,
        ?string $description = null,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = []
    ): ?self {
        try {
            // Try to get the authenticated admin if no actor provided
            if ($actor === null) {
                $actor = auth('admin')->user();
            }
            
            return self::create([
                'type' => $type,
                'title' => $title,
                'description' => $description,
                'actor_type' => $actor ? get_class($actor) : null,
                'actor_id' => $actor?->getKey(),
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id' => $subject?->getKey(),
                'properties' => $properties,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't throw - activity logging should never break the main operation
            \Log::warning('Failed to log activity: ' . $e->getMessage(), [
                'type' => $type,
                'title' => $title,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Transform activity log data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'description' => $this->description,
            'timestamp' => $this->created_at?->toIso8601String(),
            'actor' => $this->actor ? [
                'name' => $this->actor->name ?? 'System',
                'avatar' => $this->actor->avatar ?? null,
            ] : null,
        ];
    }
}
