<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Services\EmailConfigService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BlogController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $categoryId = $request->input('category_id');
        $search = $request->input('search');

        $query = BlogPost::with('category')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate($perPage);

        return $this->paginatedResponse($posts);
    }

    public function show($slug)
    {
        $post = BlogPost::with([
            'category',
            'comments' => function ($query) {
                $query->where('status', 'approved')->latest();
            },
        ])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$post) {
            return $this->errorResponse('Blog post not found', 404);
        }

        // Increment views
        $post->increment('views');

        return $this->successResponse($post);
    }

    public function addComment(Request $request, $slug, EmailConfigService $emailConfigService)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (!$post) {
            return $this->errorResponse('Blog post not found', 404);
        }

        $validated = $request->validate([
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'content' => 'required|string|max:2000',
        ]);

        $comment = $post->comments()->create([
            'author_name' => $validated['author_name'],
            'author_email' => $validated['author_email'],
            'content' => $validated['content'],
            'status' => 'pending',
        ]);

        $this->notifyAdminOfComment($post, $comment, $emailConfigService);

        return $this->successResponse(
            $comment,
            'Comment submitted and awaiting approval.',
            201
        );
    }

    private function notifyAdminOfComment(BlogPost $post, $comment, EmailConfigService $emailConfigService): void
    {
        try {
            $emailConfigService->apply();
            $adminEmail = $emailConfigService->getAdminNotificationEmail();
            if (!$adminEmail) {
                return;
            }

            Mail::raw(
                "A new blog comment is awaiting moderation.\n\nPost: {$post->title}\nAuthor: {$comment->author_name}\nEmail: {$comment->author_email}\nComment:\n{$comment->content}",
                function ($message) use ($adminEmail, $comment) {
                    $message->to($adminEmail)
                        ->subject('New Blog Comment Awaiting Moderation')
                        ->replyTo($comment->author_email, $comment->author_name);
                }
            );
        } catch (\Throwable $e) {
            Log::error('Failed to send blog comment notification email.', [
                'post_id' => $post->id,
                'comment_id' => $comment->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function categories()
    {
        $categories = BlogCategory::where('post_count', '>', 0)
            ->orderBy('name', 'asc')
            ->get();

        return $this->successResponse($categories);
    }
}
