<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends BaseApiController
{
    /**
     * List all contact messages.
     * GET /admin/contacts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $contacts = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse($contacts->through(fn ($c) => $c->toApiResponse()));
    }

    /**
     * Get contact message details.
     * GET /admin/contacts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        return $this->successResponse($contact->toApiResponse());
    }

    /**
     * Mark contact as read.
     * POST /admin/contacts/{id}/read
     */
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['status' => 'read']);

        ActivityLog::log(
            'contact_read',
            'Contact marked as read',
            "Contact from '{$contact->name}' was marked as read",
            $request->user(),
            $contact
        );

        return $this->successResponse($contact->fresh()->toApiResponse(), 'Contact marked as read.');
    }

    /**
     * Mark contact as unread.
     * POST /admin/contacts/{id}/unread
     */
    public function markAsUnread(Request $request, string $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['status' => 'unread']);

        return $this->successResponse($contact->fresh()->toApiResponse(), 'Contact marked as unread.');
    }

    /**
     * Delete contact message.
     * DELETE /admin/contacts/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contactName = $contact->name;

        $contact->delete();

        ActivityLog::log(
            'contact_deleted',
            'Contact deleted',
            "Contact from '{$contactName}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Contact deleted successfully.');
    }
}
