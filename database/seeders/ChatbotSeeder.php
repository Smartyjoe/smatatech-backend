<?php

namespace Database\Seeders;

use App\Models\ChatbotConfig;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ChatbotConfig::updateOrCreate(
            ['name' => 'Smatatech AI Assistant'],
            [
            'greeting_message' => 'Hello! How can I help you today?',
            'initial_message' => 'I can answer questions about our services, case studies, and how we can help your business grow.',
            'fallback_message' => 'I am sorry, I do not have information about that. Please contact us at info@smatatech.com.ng or use the contact form.',
            'personality_tone' => 'professional',
            'system_prompt' => 'You are a helpful AI assistant for Smatatech Technologies. Answer questions about services, case studies, and business solutions professionally and concisely.',
            'allowed_topics' => [
                'Services',
                'Case Studies',
                'Technologies',
                'Pricing',
                'Contact Information',
                'Company Information',
            ],
            'restricted_topics' => [
                'Personal Information',
                'Confidential Data',
                'Medical Advice',
                'Legal Advice',
            ],
            'is_enabled' => true,
        ]
        );
    }
}
