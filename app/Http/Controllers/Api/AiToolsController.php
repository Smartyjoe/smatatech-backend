<?php

namespace App\Http\Controllers\Api;

use App\Models\AiTool;
use App\Models\AiUsageLog;
use App\Models\Credit;
use App\Models\CreditTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiToolsController extends BaseApiController
{
    /**
     * List available AI tools.
     * GET /ai/tools
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $tools = AiTool::active()
            ->get()
            ->filter(function ($tool) use ($user) {
                // Filter based on user role
                $roleHierarchy = [
                    'premium' => ['premium', 'subscriber', 'user'],
                    'subscriber' => ['subscriber', 'user'],
                    'user' => ['user'],
                ];
                
                $allowedRoles = $roleHierarchy[$user->role] ?? ['user'];
                return in_array($tool->required_role, $allowedRoles);
            })
            ->values()
            ->map(fn ($tool) => $tool->toApiResponse());

        return $this->successResponse($tools);
    }

    /**
     * Get user's credit balance.
     * GET /ai/credits
     */
    public function credits(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $credit = Credit::firstOrCreate(
            ['user_id' => $user->id],
            ['available' => $user->credits, 'used' => 0, 'total' => $user->credits]
        );

        return $this->successResponse($credit->toApiResponse());
    }

    /**
     * Purchase credits.
     * POST /ai/credits/purchase
     * 
     * Note: This is a scaffold. Payment integration needed.
     */
    public function purchaseCredits(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:10|max:10000',
            'paymentMethod' => 'required|string',
        ]);

        // TODO: Implement payment processing
        // This is just a scaffold for future implementation

        return $this->errorResponse(
            'Credit purchase is not yet available. This feature is coming soon.',
            [],
            501
        );
    }

    /**
     * Get usage history.
     * GET /ai/usage
     */
    public function usage(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $perPage = min($request->get('per_page', 15), 100);
        
        $usageLogs = AiUsageLog::with('aiTool')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse($usageLogs->through(fn ($log) => $log->toApiResponse()));
    }

    /**
     * Execute AI tool.
     * POST /ai/tools/{id}/execute
     * 
     * Note: This is a scaffold. AI integration needed.
     */
    public function execute(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $tool = AiTool::active()->findOrFail($id);

        $request->validate([
            'input' => 'required|string|max:10000',
            'options' => 'nullable|array',
        ]);

        // Check credits
        $credit = Credit::firstOrCreate(
            ['user_id' => $user->id],
            ['available' => $user->credits, 'used' => 0, 'total' => $user->credits]
        );

        if ($credit->available < $tool->credits_per_use) {
            return $this->errorResponse(
                'Insufficient credits. Please purchase more credits to use this tool.',
                ['required' => $tool->credits_per_use, 'available' => $credit->available],
                402
            );
        }

        // TODO: Implement actual AI tool execution
        // This is a scaffold that returns a placeholder response

        // Deduct credits
        $credit->decrement('available', $tool->credits_per_use);
        $credit->increment('used', $tool->credits_per_use);

        // Log transaction
        CreditTransaction::create([
            'user_id' => $user->id,
            'amount' => -$tool->credits_per_use,
            'type' => 'usage',
            'description' => "Used {$tool->name}",
            'metadata' => ['tool_id' => $tool->id],
        ]);

        // Log usage
        $usageLog = AiUsageLog::create([
            'user_id' => $user->id,
            'ai_tool_id' => $tool->id,
            'input' => $request->get('input'),
            'output' => 'AI tool execution is not yet implemented. This is a placeholder response.',
            'credits_used' => $tool->credits_per_use,
            'status' => 'completed',
            'execution_time_ms' => 0,
            'metadata' => $request->get('options'),
        ]);

        return $this->successResponse([
            'output' => 'AI tool execution is not yet implemented. This is a placeholder response.',
            'creditsUsed' => $tool->credits_per_use,
            'remainingCredits' => $credit->fresh()->available,
        ]);
    }
}
