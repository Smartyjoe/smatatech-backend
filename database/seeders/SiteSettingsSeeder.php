<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Smatatech Technologies', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'We build intelligent digital solutions that transform businesses.', 'type' => 'text'],
            ['key' => 'contact_email', 'value' => 'info@smatatech.com.ng', 'type' => 'text'],
            ['key' => 'contact_phone', 'value' => '+234 801 234 5678', 'type' => 'text'],
            ['key' => 'address', 'value' => 'Lagos, Nigeria', 'type' => 'text'],
            ['key' => 'social_facebook', 'value' => 'https://facebook.com/smatatech', 'type' => 'text'],
            ['key' => 'social_twitter', 'value' => 'https://twitter.com/smatatech', 'type' => 'text'],
            ['key' => 'social_linkedin', 'value' => 'https://linkedin.com/company/smatatech', 'type' => 'text'],
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/smatatech', 'type' => 'text'],
            ['key' => 'social_youtube', 'value' => 'https://youtube.com/@smatatech', 'type' => 'text'],
            ['key' => 'logo', 'value' => '/logo.png', 'type' => 'text'],
            ['key' => 'favicon', 'value' => '/favicon.ico', 'type' => 'text'],
            ['key' => 'footer_text', 'value' => '(c) 2026 Smatatech Technologies. All rights reserved.', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            SiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
