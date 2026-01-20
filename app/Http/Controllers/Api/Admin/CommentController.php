<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends BaseApiController
{
    /**
     * List all comments.
     * GET /admin/comments
     */
    public function index(Request $request): JsonResponse
    {
        $query = Comment::with(['post', 'user']);

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by post
        if ($postId = $request->get('post_id')) {
            $query->where('post_id', $postId);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $comments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse($comments->through(fn ($comment) => $comment->toApiResponse()));
    }

    /**
     * Get comment details.
     * GET /admin/comments/{id}
     */
    public function show(string $id): JsonResponse
    {
        $comment = Comment::with(['post', 'user'])->findOrFail($id);

        return $this->successResponse($comment->toApiResponse());
    }

    /**
     * Approve comment.
     * POST /admin/comments/{id}/approve
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'approved']);

        ActivityLog::log(
            'comment_approved',
            'Comment approved',
            "Comment on post '{$comment->post->title}' was approved",
            $request->user(),
            $comment
        );

        return $this->successResponse($comment->fresh()->load(['post', 'user'])->toApiResponse(), 'Comment approved successfully.');
    }

    /**
     * Reject comment.
     * POST /admin/comments/{id}/reject
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $comment = Comment::findOrFail($id);
        $comment->update(['status' => 'rejected']);

        ActivityLog::log(
            'comment_rejected',
            'Comment rejected',
            "Comment on post '{$comment->post->title}' was rejected",
            $request->user(),
            $comment
        );

        return $this->successResponse($comment->fresh()->load(['post', 'user'])->toApiResponse(), 'Comment rejected successfully.');
    }

    /**
     * Delete comment.
     * DELETE /admin/comments/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $comment = Comment::with('post')->findOrFail($id);
        $postTitle = $comment->post->title ?? 'Unknown';

        $comment->delete();

        ActivityLog::log(
            'comment_deleted',
            'Comment deleted',
            "Comment on post '{$postTitle}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Comment deleted successfully.');
    }
}
