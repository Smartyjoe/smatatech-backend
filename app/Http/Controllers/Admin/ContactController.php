<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $read = $request->input('read');

        $query = ContactMessage::query()->latest();

        if ($read !== null) {
            $query->where('read', filter_var($read, FILTER_VALIDATE_BOOLEAN));
        }

        $contacts = $query->paginate($perPage);

        return $this->paginatedResponse($contacts);
    }

    public function update(Request $request, $id)
    {
        $contact = ContactMessage::findOrFail($id);

        $validated = $request->validate([
            'read' => 'required|boolean',
        ]);

        $contact->update(['read' => $validated['read']]);

        return $this->successResponse($contact, 'Contact message updated successfully');
    }

    public function destroy($id)
    {
        $contact = ContactMessage::findOrFail($id);
        $contact->delete();

        return $this->successResponse(null, 'Contact message deleted successfully');
    }
}
