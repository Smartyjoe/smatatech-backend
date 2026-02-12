<?php

namespace Database\Seeders;

use App\Models\ChatbotTraining;
use Illuminate\Database\Seeder;

class ChatbotTrainingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'title' => 'What services do you offer?',
                'content' => 'We provide web development, AI solutions, mobile app development, and digital strategy.',
                'category' => 'faq',
                'priority' => 10,
                'is_active' => true,
            ],
            [
                'title' => 'How can I contact your team?',
                'content' => 'You can reach us at info@smatatech.com.ng or use the contact form on our website.',
                'category' => 'contact',
                'priority' => 9,
                'is_active' => true,
            ],
            [
                'title' => 'Do you build custom AI solutions?',
                'content' => 'Yes. We design and build AI solutions tailored to your business needs.',
                'category' => 'ai',
                'priority' => 8,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            ChatbotTraining::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
