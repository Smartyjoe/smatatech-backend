<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BlogCommentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $status = $request->input('status');
        $postId = $request->input('post_id');

        $query = BlogComment::with('post')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($postId) {
            $query->where('post_id', $postId);
        }

        $comments = $query->paginate($perPage);

        return $this->paginatedResponse($comments);
    }

    public function update(Request $request, $id)
    {
        $comment = BlogComment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:approved,pending,rejected',
        ]);

        $comment->update(['status' => $validated['status']]);

        return $this->successResponse($comment, 'Comment updated successfully');
    }

    public function destroy($id)
    {
        $comment = BlogComment::findOrFail($id);
        $comment->delete();

        return $this->successResponse(null, 'Comment deleted successfully');
    }
}
