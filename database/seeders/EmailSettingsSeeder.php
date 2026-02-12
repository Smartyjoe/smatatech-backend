<?php

namespace Database\Seeders;

use App\Models\EmailSetting;
use Illuminate\Database\Seeder;

class EmailSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'mailer' => 'smtp',
            'host' => 'smtp-relay.brevo.com',
            'port' => '587',
            'username' => null,
            'password' => null,
            'encryption' => 'tls',
            'from_address' => 'info@smatatech.com.ng',
            'from_name' => 'Smatatech',
            'brevo_api_key' => null,
            'brevo_sender_name' => 'Smatatech',
            'brevo_sender_email' => 'info@smatatech.com.ng',
            'brevo_reply_to' => 'info@smatatech.com.ng',
        ];

        foreach ($settings as $key => $value) {
            EmailSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
