<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\BrevoConfig;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EmailController extends BaseApiController
{
    /**
     * Get email settings.
     * GET /admin/email/settings
     */
    public function getSettings(): JsonResponse
    {
        $settings = EmailSetting::current();

        return $this->successResponse($settings->toApiResponse());
    }

    /**
     * Update email settings.
     * PUT /admin/email/settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'fromName' => 'nullable|string|max:255',
            'fromEmail' => 'nullable|email|max:255',
            'replyTo' => 'nullable|email|max:255',
            'smtpHost' => 'nullable|string|max:255',
            'smtpPort' => 'nullable|integer',
            'smtpUsername' => 'nullable|string|max:255',
            'smtpPassword' => 'nullable|string',
            'smtpEncryption' => 'nullable|string|in:tls,ssl',
        ]);

        $settings = EmailSetting::current();

        $updateData = [];
        if (array_key_exists('fromName', $validated)) $updateData['from_name'] = $validated['fromName'];
        if (array_key_exists('fromEmail', $validated)) $updateData['from_email'] = $validated['fromEmail'];
        if (array_key_exists('replyTo', $validated)) $updateData['reply_to'] = $validated['replyTo'];
        if (array_key_exists('smtpHost', $validated)) $updateData['smtp_host'] = $validated['smtpHost'];
        if (array_key_exists('smtpPort', $validated)) $updateData['smtp_port'] = $validated['smtpPort'];
        if (array_key_exists('smtpUsername', $validated)) $updateData['smtp_username'] = $validated['smtpUsername'];
        if (array_key_exists('smtpPassword', $validated)) $updateData['smtp_password'] = $validated['smtpPassword'];
        if (array_key_exists('smtpEncryption', $validated)) $updateData['smtp_encryption'] = $validated['smtpEncryption'];

        $settings->update($updateData);

        ActivityLog::log(
            'email_settings_updated',
            'Email settings updated',
            'Email configuration was updated',
            $request->user()
        );

        return $this->successResponse($settings->fresh()->toApiResponse(), 'Email settings updated.');
    }

    /**
     * List email templates.
     * GET /admin/email/templates
     */
    public function getTemplates(): JsonResponse
    {
        $templates = EmailTemplate::orderBy('name')->get();

        return $this->successResponse($templates->map(fn ($t) => $t->toApiResponse()));
    }

    /**
     * Get email template details.
     * GET /admin/email/templates/{id}
     */
    public function getTemplate(string $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        return $this->successResponse($template->toApiResponse());
    }

    /**
     * Update email template.
     * PUT /admin/email/templates/{id}
     */
    public function updateTemplate(Request $request, string $id): JsonResponse
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'variables' => 'nullable|array',
            'isActive' => 'sometimes|boolean',
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['subject'])) $updateData['subject'] = $validated['subject'];
        if (isset($validated['body'])) $updateData['body'] = $validated['body'];
        if (array_key_exists('variables', $validated)) $updateData['variables'] = $validated['variables'];
        if (isset($validated['isActive'])) $updateData['is_active'] = $validated['isActive'];

        $template->update($updateData);

        ActivityLog::log(
            'email_template_updated',
            'Email template updated',
            "Email template '{$template->name}' was updated",
            $request->user(),
            $template
        );

        return $this->successResponse($template->fresh()->toApiResponse(), 'Email template updated.');
    }

    /**
     * Get Brevo config.
     * GET /admin/email/brevo
     */
    public function getBrevoConfig(): JsonResponse
    {
        $config = BrevoConfig::current();

        return $this->successResponse($config->toApiResponse());
    }

    /**
     * Update Brevo config.
     * PUT /admin/email/brevo
     */
    public function updateBrevoConfig(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'apiKey' => 'nullable|string',
            'senderName' => 'nullable|string|max:255',
            'senderEmail' => 'nullable|email|max:255',
            'isEnabled' => 'sometimes|boolean',
        ]);

        $config = BrevoConfig::current();

        $updateData = [];
        if (array_key_exists('apiKey', $validated)) $updateData['api_key'] = $validated['apiKey'];
        if (array_key_exists('senderName', $validated)) $updateData['sender_name'] = $validated['senderName'];
        if (array_key_exists('senderEmail', $validated)) $updateData['sender_email'] = $validated['senderEmail'];
        if (isset($validated['isEnabled'])) $updateData['is_enabled'] = $validated['isEnabled'];

        $config->update($updateData);

        ActivityLog::log(
            'brevo_config_updated',
            'Brevo configuration updated',
            'Brevo email service settings were updated',
            $request->user()
        );

        return $this->successResponse($config->fresh()->toApiResponse(), 'Brevo configuration updated.');
    }

    /**
     * Test Brevo connection.
     * POST /admin/email/brevo/test
     */
    public function testBrevoConnection(Request $request): JsonResponse
    {
        $config = BrevoConfig::current();

        if (!$config->api_key) {
            return $this->errorResponse('Brevo API key is not configured.', [], 400);
        }

        try {
            $response = Http::withHeaders([
                'api-key' => $config->api_key,
                'accept' => 'application/json',
            ])->get('https://api.brevo.com/v3/account');

            if ($response->successful()) {
                return $this->successResponse([
                    'connected' => true,
                    'account' => $response->json(),
                ], 'Brevo connection successful.');
            }

            return $this->errorResponse('Brevo connection failed: ' . $response->body(), [], 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Brevo connection failed: ' . $e->getMessage(), [], 500);
        }
    }
}
