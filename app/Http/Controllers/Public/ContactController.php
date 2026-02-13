<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\EmailTemplate;
use App\Services\EmailConfigService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request, EmailConfigService $emailConfigService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'project_type' => 'nullable|string|max:255',
            'budget' => 'nullable|string|max:255',
            'services' => 'nullable|array',
            'message' => 'required|string|max:5000',
        ]);

        $contact = ContactMessage::create($validated);

        $this->sendContactEmails($contact, $emailConfigService);

        return $this->successResponse(
            ['id' => $contact->id],
            'Thank you for contacting us! We will get back to you soon.',
            201
        );
    }

    private function sendContactEmails(ContactMessage $contact, EmailConfigService $emailConfigService): void
    {
        try {
            $config = $emailConfigService->apply();
            $data = [
                'name' => $contact->name,
                'email' => $contact->email,
                'company' => $contact->company ?? '',
                'phone' => $contact->phone ?? '',
                'project_type' => $contact->project_type ?? '',
                'budget' => $contact->budget ?? '',
                'services' => is_array($contact->services) ? implode(', ', $contact->services) : '',
                'message' => $contact->message,
            ];

            // Auto-reply to submitter
            $autoReplyTemplate = EmailTemplate::where('name', 'Contact Auto-Reply')->first();
            $autoReplySubject = $this->renderTemplate(
                $autoReplyTemplate?->subject ?: 'Thanks for contacting Smatatech',
                $data
            );
            $autoReplyBody = $this->renderTemplate(
                $autoReplyTemplate?->body ?: "Hello {{name}},\n\nThank you for contacting us.",
                $data
            );

            Mail::raw($this->asText($autoReplyBody), function ($message) use ($contact, $autoReplySubject, $config) {
                $message->to($contact->email)
                    ->subject($autoReplySubject);
                if (!empty($config['reply_to'])) {
                    $message->replyTo($config['reply_to'], $config['from_name']);
                }
            });

            // Admin notification
            $adminEmail = $emailConfigService->getAdminNotificationEmail();
            if ($adminEmail) {
                $adminTemplate = EmailTemplate::where('name', 'Admin Notification')->first();
                $adminSubject = $this->renderTemplate(
                    $adminTemplate?->subject ?: 'New Contact Form Submission',
                    $data
                );
                $adminBody = $this->renderTemplate(
                    $adminTemplate?->body ?: "New contact form submission from {{name}} ({{email}}).\n\n{{message}}",
                    $data
                );

                Mail::raw($this->asText($adminBody), function ($message) use ($adminEmail, $adminSubject, $contact) {
                    $message->to($adminEmail)
                        ->subject($adminSubject)
                        ->replyTo($contact->email, $contact->name);
                });
            }
        } catch (\Throwable $e) {
            Log::error('Failed to send contact emails.', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function renderTemplate(string $template, array $data): string
    {
        $output = $template;
        foreach ($data as $key => $value) {
            $output = str_replace('{{' . $key . '}}', (string) $value, $output);
        }
        return $output;
    }

    private function asText(string $value): string
    {
        $plain = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $value));
        return html_entity_decode($plain, ENT_QUOTES | ENT_HTML5);
    }
}
