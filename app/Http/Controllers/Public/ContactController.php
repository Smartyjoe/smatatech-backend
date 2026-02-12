<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
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

        // TODO: Send email notification to admin
        // Mail::to(config('mail.admin_email'))->send(new ContactMessageReceived($contact));

        return $this->successResponse(
            ['id' => $contact->id],
            'Thank you for contacting us! We will get back to you soon.',
            201
        );
    }
}
