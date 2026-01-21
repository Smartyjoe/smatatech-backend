<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class EmailSetting extends Model
{
    use HasUuid;

    protected $fillable = [
        'from_name',
        'from_email',
        'reply_to',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    /**
     * Encrypt SMTP password before saving.
     */
    public function setSmtpPasswordAttribute($value): void
    {
        if ($value) {
            $this->attributes['smtp_password'] = Crypt::encryptString($value);
        }
    }

    /**
     * Decrypt SMTP password when retrieving.
     */
    public function getSmtpPasswordAttribute($value): ?string
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
     * Get the current settings (singleton pattern).
     */
    public static function current(): self
    {
        return self::first() ?? self::create([]);
    }

    /**
     * Transform settings data for API response.
     * Returns empty strings instead of null for form compatibility.
     */
    public function toApiResponse(): array
    {
        return [
            'fromName' => $this->from_name ?? '',
            'fromEmail' => $this->from_email ?? '',
            'replyTo' => $this->reply_to ?? '',
            'smtpHost' => $this->smtp_host ?? '',
            'smtpPort' => $this->smtp_port ?? 587,
            'smtpUsername' => $this->smtp_username ?? '',
            'smtpEncryption' => $this->smtp_encryption ?? 'tls',
        ];
    }
}
