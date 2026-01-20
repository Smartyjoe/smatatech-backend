<?php

namespace Database\Seeders;

use App\Models\ChatbotConfig;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Default site settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Smatatech Technologies', 'type' => 'string', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Digital Solutions Company', 'type' => 'string', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'hello@smatatech.com', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+1234567890', 'type' => 'string', 'group' => 'contact'],
            ['key' => 'address', 'value' => '123 Tech Street, Innovation City', 'type' => 'string', 'group' => 'contact'],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'facebook' => 'https://facebook.com/smatatech',
                    'twitter' => 'https://twitter.com/smatatech',
                    'linkedin' => 'https://linkedin.com/company/smatatech',
                    'instagram' => 'https://instagram.com/smatatech',
                ]),
                'type' => 'json',
                'group' => 'social'
            ],
        ];

        foreach ($settings as $setting) {
            SiteSetting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Default chatbot config
        ChatbotConfig::firstOrCreate(
            ['id' => ChatbotConfig::first()?->id ?? \Illuminate\Support\Str::uuid()],
            [
                'system_prompt' => 'You are a helpful assistant for Smatatech Technologies, a digital solutions company specializing in web development, AI solutions, and digital transformation.',
                'personality_tone' => 'professional',
                'allowed_topics' => ['web development', 'AI solutions', 'pricing', 'services', 'contact'],
                'restricted_topics' => ['competitors', 'internal processes'],
                'greeting_message' => 'Hello! Welcome to Smatatech Technologies. How can I help you today?',
                'fallback_message' => 'I apologize, but I\'m not sure how to help with that. Would you like to speak with our team directly?',
                'is_enabled' => true,
                'version_label' => 'v1.0',
            ]
        );

        $this->command->info('Site settings seeded successfully!');
    }
}
