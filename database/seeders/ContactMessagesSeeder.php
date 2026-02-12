<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $messages = [
            [
                'name' => 'Emily Brown',
                'email' => 'emily@techcompany.com',
                'company' => 'Tech Company Ltd',
                'phone' => '+234 801 111 2222',
                'project_type' => 'new',
                'budget' => '25000',
                'services' => ['web', 'ai'],
                'message' => 'We want to build an AI-powered customer service platform. Can you help?',
                'read' => false,
            ],
            [
                'name' => 'James Wilson',
                'email' => 'james@startup.io',
                'company' => 'Startup.io',
                'phone' => '+234 801 333 4444',
                'project_type' => 'consulting',
                'budget' => '5000',
                'services' => ['consulting'],
                'message' => 'Need advice on selecting the right tech stack for our product.',
                'read' => true,
            ],
        ];

        foreach ($messages as $message) {
            ContactMessage::updateOrCreate(
                ['email' => $message['email'], 'message' => $message['message']],
                $message
            );
        }
    }
}
