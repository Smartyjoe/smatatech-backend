<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class BrevoConfig extends Model
{
    use HasUuid;

    protected $table = 'brevo_config';

    protected $fillable = [
        'api_key',
        'sender_name',
        'sender_email',
        'is_enabled',
    ];

    protected $hidden = [
        'api_key',
    ];

    /**
     * Encrypt API key before saving.
     */
    public function setApiKeyAttribute($value): void
    {
        if ($value) {
            $this->attributes['api_key'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt API key when retrieving.
     */
    public function getApiKeyAttribute($value): ?string
    {
        if ($value) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Get the current config (singleton pattern).
     */
    public static function current(): self
    {
        return self::first() ?? self::create([]);
    }

    /**
     * Transform config data for API response.
     * Returns empty strings instead of null for form compatibility.
     */
    public function toApiResponse(): array
    {
        return [
            'senderName' => $this->sender_name ?? '',
            'senderEmail' => $this->sender_email ?? '',
            'isEnabled' => (bool) $this->is_enabled,
            'hasApiKey' => !empty($this->api_key),
        ];
    }
}
