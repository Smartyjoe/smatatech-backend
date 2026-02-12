<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use App\Models\EmailTemplate;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    use ApiResponse;

    public function getSettings()
    {
        $settings = [
            'mailer' => EmailSetting::get('mailer', 'smtp'),
            'host' => EmailSetting::get('host', 'smtp-relay.brevo.com'),
            'port' => EmailSetting::get('port', '587'),
            'username' => EmailSetting::get('username'),
            'password' => EmailSetting::get('password'),
            'encryption' => EmailSetting::get('encryption', 'tls'),
            'from_address' => EmailSetting::get('from_address', 'noreply@smatatech.com'),
            'from_name' => EmailSetting::get('from_name', 'Smatatech'),
            'brevo_api_key' => EmailSetting::get('brevo_api_key'),
            'brevo_sender_name' => EmailSetting::get('brevo_sender_name'),
            'brevo_sender_email' => EmailSetting::get('brevo_sender_email'),
            'brevo_reply_to' => EmailSetting::get('brevo_reply_to'),
        ];

        return $this->successResponse($settings);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'mailer' => 'nullable|string',
            'host' => 'nullable|string',
            'port' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'encryption' => 'nullable|string',
            'from_address' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            EmailSetting::set($key, $value);
        }

        return $this->successResponse(null, 'Email settings updated successfully');
    }

    public function updateBrevoConfig(Request $request)
    {
        $validated = $request->validate([
            'brevo_api_key' => 'nullable|string',
            'brevo_sender_name' => 'nullable|string',
            'brevo_sender_email' => 'nullable|email',
            'brevo_reply_to' => 'nullable|email',
        ]);

        foreach ($validated as $key => $value) {
            EmailSetting::set($key, $value);
        }

        return $this->successResponse(null, 'Brevo configuration updated successfully');
    }

    public function getTemplates(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $templates = EmailTemplate::latest()->paginate($perPage);

        return $this->paginatedResponse($templates);
    }

    public function createTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            EmailTemplate::where('is_default', true)->update(['is_default' => false]);
        }

        $template = EmailTemplate::create($validated);

        return $this->successResponse($template, 'Email template created successfully', 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = EmailTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'subject' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'variables' => 'nullable|array',
            'is_default' => 'nullable|boolean',
        ]);

        // If setting as default, unset other defaults
        if (isset($validated['is_default']) && $validated['is_default']) {
            EmailTemplate::where('id', '!=', $id)->update(['is_default' => false]);
        }

        $template->update($validated);

        return $this->successResponse($template, 'Email template updated successfully');
    }

    public function deleteTemplate($id)
    {
        $template = EmailTemplate::findOrFail($id);
        $template->delete();

        return $this->successResponse(null, 'Email template deleted successfully');
    }

    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'to' => 'required|email',
            'subject' => 'nullable|string',
            'message' => 'nullable|string',
        ]);

        try {
            $host = EmailSetting::get('host', 'smtp-relay.brevo.com');
            $port = EmailSetting::get('port', '587');
            $username = EmailSetting::get('username');
            $password = EmailSetting::get('password');
            $encryption = EmailSetting::get('encryption', 'tls');
            $fromAddress = EmailSetting::get('from_address', 'noreply@smatatech.com.ng');
            $fromName = EmailSetting::get('from_name', 'Smatatech');
            $brevoKey = EmailSetting::get('brevo_api_key');

            if (!$username && $brevoKey) {
                $username = 'apikey';
            }
            if (!$password && $brevoKey) {
                $password = $brevoKey;
            }

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => (int) $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.timeout' => 10,
                'mail.from.address' => $fromAddress,
                'mail.from.name' => $fromName,
            ]);

            Mail::raw(
                $validated['message'] ?? 'This is a test email from Smatatech Backend.',
                function ($message) use ($validated) {
                    $message->to($validated['to'])
                           ->subject($validated['subject'] ?? 'Test Email from Smatatech');
                }
            );

            return $this->successResponse(null, 'Test email sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send test email: ' . $e->getMessage(), 500);
        }
    }
}
