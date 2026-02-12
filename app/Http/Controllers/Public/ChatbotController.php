<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ChatbotService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    use ApiResponse;

    public function config(ChatbotService $chatbotService)
    {
        return $this->successResponse($chatbotService->getPublicConfig());
    }

    public function message(Request $request, ChatbotService $chatbotService)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:4000',
            'history' => 'nullable|array',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
        ]);

        $reply = $chatbotService->reply(
            $validated['message'],
            $validated['history'] ?? []
        );

        return $this->successResponse([
            'reply' => $reply,
            'conversationId' => Str::uuid()->toString(),
        ]);
    }
}

