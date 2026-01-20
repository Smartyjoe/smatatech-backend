<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    use HasUuid;

    protected $fillable = [
        'user_id',
        'amount',
        'type',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
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
     * Transform transaction data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'description' => $this->description,
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
