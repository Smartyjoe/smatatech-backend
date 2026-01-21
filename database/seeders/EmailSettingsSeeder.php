<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmailSettingsSeeder extends Seeder
{
    /**
     * Seed email settings, templates, and Brevo config with defaults.
     */
    public function run(): void
    {
        $this->seedEmailSettings();
        $this->seedEmailTemplates();
        $this->seedBrevoConfig();
        
        $this->command->info('Email settings, templates, and Brevo config seeded successfully!');
    }

    /**
     * Seed default email settings.
     */
    private function seedEmailSettings(): void
    {
        // Only seed if no settings exist
        if (DB::table('email_settings')->count() > 0) {
            // Update existing with defaults for null fields
            DB::table('email_settings')
                ->whereNull('from_name')
                ->update(['from_name' => 'Smatatech']);
            
            DB::table('email_settings')
                ->whereNull('from_email')
                ->update(['from_email' => 'noreply@smatatech.com.ng']);
            
            $this->command->info('Email settings updated with defaults.');
            return;
        }

        DB::table('email_settings')->insert([
            'id' => Str::uuid()->toString(),
            'from_name' => 'Smatatech',
            'from_email' => 'noreply@smatatech.com.ng',
            'reply_to' => 'hello@smatatech.com.ng',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Email settings seeded.');
    }

    /**
     * Seed default email templates.
     */
    private function seedEmailTemplates(): void
    {
        $templates = [
            [
                'name' => 'Welcome Email',
                'slug' => 'welcome',
                'subject' => 'Welcome to {{site_name}}!',
                'body' => '<h1>Welcome, {{user_name}}!</h1>
<p>Thank you for registering at {{site_name}}. We are excited to have you on board.</p>
<p>You can now access all our features and services.</p>
<p>Best regards,<br>The {{site_name}} Team</p>',
                'variables' => json_encode(['user_name', 'site_name', 'login_url']),
            ],
            [
                'name' => 'Password Reset',
                'slug' => 'password-reset',
                'subject' => 'Reset Your Password - {{site_name}}',
                'body' => '<h1>Password Reset Request</h1>
<p>Hello {{user_name}},</p>
<p>We received a request to reset your password. Click the link below to set a new password:</p>
<p><a href="{{reset_url}}">Reset Password</a></p>
<p>This link will expire in 60 minutes.</p>
<p>If you did not request this, please ignore this email.</p>
<p>Best regards,<br>The {{site_name}} Team</p>',
                'variables' => json_encode(['user_name', 'site_name', 'reset_url']),
            ],
            [
                'name' => 'Contact Form Notification',
                'slug' => 'contact-notification',
                'subject' => 'New Contact Message from {{sender_name}}',
                'body' => '<h1>New Contact Form Submission</h1>
<p><strong>From:</strong> {{sender_name}} ({{sender_email}})</p>
<p><strong>Company:</strong> {{company}}</p>
<p><strong>Phone:</strong> {{phone}}</p>
<p><strong>Message:</strong></p>
<blockquote>{{message}}</blockquote>
<p>You can view and manage this message in your <a href="{{admin_url}}">admin dashboard</a>.</p>',
                'variables' => json_encode(['sender_name', 'sender_email', 'company', 'phone', 'message', 'admin_url']),
            ],
            [
                'name' => 'Contact Form Auto-Reply',
                'slug' => 'contact-auto-reply',
                'subject' => 'Thank you for contacting {{site_name}}',
                'body' => '<h1>Thank You for Reaching Out!</h1>
<p>Dear {{sender_name}},</p>
<p>We have received your message and appreciate you taking the time to contact us.</p>
<p>Our team will review your inquiry and get back to you within 24-48 hours.</p>
<p>Here is a copy of your message:</p>
<blockquote>{{message}}</blockquote>
<p>Best regards,<br>The {{site_name}} Team</p>',
                'variables' => json_encode(['sender_name', 'site_name', 'message']),
            ],
            [
                'name' => 'Newsletter Subscription',
                'slug' => 'newsletter-welcome',
                'subject' => 'Welcome to {{site_name}} Newsletter',
                'body' => '<h1>Thanks for Subscribing!</h1>
<p>You have successfully subscribed to the {{site_name}} newsletter.</p>
<p>You will now receive updates about our latest services, case studies, and tech insights.</p>
<p>If you wish to unsubscribe, click <a href="{{unsubscribe_url}}">here</a>.</p>
<p>Best regards,<br>The {{site_name}} Team</p>',
                'variables' => json_encode(['site_name', 'unsubscribe_url']),
            ],
        ];

        foreach ($templates as $template) {
            // Check if template already exists
            $exists = DB::table('email_templates')->where('slug', $template['slug'])->exists();
            
            if (!$exists) {
                DB::table('email_templates')->insert([
                    'id' => Str::uuid()->toString(),
                    'name' => $template['name'],
                    'slug' => $template['slug'],
                    'subject' => $template['subject'],
                    'body' => $template['body'],
                    'variables' => $template['variables'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Email templates seeded: ' . count($templates));
    }

    /**
     * Seed default Brevo configuration.
     */
    private function seedBrevoConfig(): void
    {
        // Only seed if no config exists
        if (DB::table('brevo_config')->count() > 0) {
            $this->command->info('Brevo config already exists.');
            return;
        }

        DB::table('brevo_config')->insert([
            'id' => Str::uuid()->toString(),
            'api_key' => '',
            'sender_name' => 'Smatatech',
            'sender_email' => 'noreply@smatatech.com.ng',
            'is_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Brevo config seeded with defaults.');
    }
}
