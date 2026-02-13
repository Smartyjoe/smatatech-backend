<?php

namespace App\Services;

use App\Models\EmailSetting;
use App\Models\SiteSetting;

class EmailConfigService
{
    public function apply(): array
    {
        $host = EmailSetting::get('host', config('mail.mailers.smtp.host', 'smtp-relay.brevo.com'));
        $port = (int) EmailSetting::get('port', config('mail.mailers.smtp.port', 587));
        $username = EmailSetting::get('username', config('mail.mailers.smtp.username'));
        $password = EmailSetting::get('password', config('mail.mailers.smtp.password'));
        $encryption = EmailSetting::get('encryption', config('mail.mailers.smtp.encryption', 'tls'));

        $fromAddress = EmailSetting::get('from_address', config('mail.from.address'));
        $fromName = EmailSetting::get('from_name', config('mail.from.name'));
        $replyTo = EmailSetting::get('reply_to', EmailSetting::get('brevo_reply_to', null));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $host,
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $username,
            'mail.mailers.smtp.password' => $password,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.timeout' => 15,
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
            'mail.reply_to.address' => $replyTo,
            'mail.reply_to.name' => $fromName,
        ]);

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'encryption' => $encryption,
            'from_address' => $fromAddress,
            'from_name' => $fromName,
            'reply_to' => $replyTo,
        ];
    }

    public function getAdminNotificationEmail(): ?string
    {
        return EmailSetting::get('admin_notification_email')
            ?: SiteSetting::get('contact_email')
            ?: EmailSetting::get('from_address')
            ?: config('mail.from.address');
    }
}

