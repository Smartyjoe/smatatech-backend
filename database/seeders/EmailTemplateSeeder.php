<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailTemplate::updateOrCreate(
            ['name' => 'Contact Auto-Reply'],
            [
                'subject' => 'Thanks for contacting Smatatech',
                'body' => "Hello {{name}},\n\nThanks for reaching out to Smatatech Technologies. We have received your message and will respond shortly.\n\nBest regards,\nSmatatech Team",
                'variables' => ['name'],
                'is_default' => false,
            ]
        );

        EmailTemplate::updateOrCreate(
            ['name' => 'Admin Notification'],
            [
                'subject' => 'New Contact Form Submission',
                'body' => "Hello Admin,\n\nYou have received a new contact form submission:\n\nName: {{name}}\nEmail: {{email}}\nCompany: {{company}}\nMessage: {{message}}\n\nPlease respond as soon as possible.",
                'variables' => ['name', 'email', 'company', 'message'],
                'is_default' => true,
            ]
        );

        EmailTemplate::updateOrCreate(
            ['name' => 'Newsletter'],
            [
                'subject' => 'Smatatech Newsletter',
                'body' => "Hello {{name}},\n\nHere is the latest update from Smatatech Technologies.\n\nBest regards,\nSmatatech Team",
                'variables' => ['name'],
                'is_default' => false,
            ]
        );
    }
}
