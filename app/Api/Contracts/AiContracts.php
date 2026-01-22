<?php

namespace App\Api\Contracts;

use App\Api\ErrorCode;

/**
 * AI Tools API Endpoint Contracts
 */
class AiContracts
{
    public static function register(): void
    {
        // List AI Tools
        ApiRegistry::register(
            EndpointContract::get('/api/ai/tools', 'List AI Tools')
                ->description('Get available AI tools based on user role')
                ->group('ai')
                ->tags('ai', 'tools')
                ->auth('sanctum')
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('name', 'string', true)
                    ->property('description', 'string', true)
                    ->property('creditCost', 'integer', true)
                    ->property('isAvailable', 'boolean', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Get Credits
        ApiRegistry::register(
            EndpointContract::get('/api/ai/credits', 'Get Credit Balance')
                ->description('Get current credit balance')
                ->group('ai')
                ->tags('ai', 'credits')
                ->auth('sanctum')
                ->response(Schema::object()
                    ->property('balance', 'integer', true, 100)
                    ->property('lifetimeUsed', 'integer', true, 500)
                    ->property('lastPurchase', 'datetime', false)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Purchase Credits
        ApiRegistry::register(
            EndpointContract::post('/api/ai/credits/purchase', 'Purchase Credits')
                ->description('Purchase additional credits')
                ->group('ai')
                ->tags('ai', 'credits', 'payments')
                ->auth('sanctum')
                ->requestBody(Schema::object()
                    ->property('amount', 'integer', true, 100, 'Number of credits')
                    ->property('paymentMethod', 'string', true, 'card', 'Payment method')
                    ->required('amount', 'paymentMethod')
                )
                ->response(Schema::object()
                    ->property('newBalance', 'integer', true)
                    ->property('transactionId', 'string', true)
                )
                ->errors(
                    ErrorCode::AUTH_REQUIRED,
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::OPERATION_FAILED
                )
        );

        // Get Usage
        ApiRegistry::register(
            EndpointContract::get('/api/ai/usage', 'Get Usage History')
                ->description('Get AI tool usage history')
                ->group('ai')
                ->tags('ai', 'usage')
                ->auth('sanctum')
                ->queryParams(Schema::object()
                    ->property('per_page', 'integer', false, 20)
                    ->property('page', 'integer', false, 1)
                )
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('toolId', 'uuid', true)
                    ->property('toolName', 'string', true)
                    ->property('creditsUsed', 'integer', true)
                    ->property('createdAt', 'datetime', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Execute Tool
        ApiRegistry::register(
            EndpointContract::post('/api/ai/tools/{id}/execute', 'Execute AI Tool')
                ->description('Execute an AI tool')
                ->group('ai')
                ->tags('ai', 'tools')
                ->auth('sanctum')
                ->pathParams(Schema::object()
                    ->property('id', 'uuid', true, null, 'Tool ID')
                )
                ->requestBody(Schema::object()
                    ->property('input', 'string', true, null, 'Input for the tool')
                    ->property('options', 'object', false, null, 'Tool-specific options')
                    ->required('input')
                )
                ->response(Schema::object()
                    ->property('result', 'string', true, 'Tool output')
                    ->property('creditsUsed', 'integer', true)
                    ->property('remainingCredits', 'integer', true)
                )
                ->errors(
                    ErrorCode::AUTH_REQUIRED,
                    ErrorCode::NOT_FOUND,
                    ErrorCode::INSUFFICIENT_CREDITS,
                    ErrorCode::VALIDATION_ERROR
                )
        );
    }
}
