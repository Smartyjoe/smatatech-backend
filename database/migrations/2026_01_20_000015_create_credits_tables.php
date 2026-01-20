<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Note: This is a scaffold for future AI tools and credit system.
     */
    public function up(): void
    {
        // User credit balances
        Schema::create('credits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->integer('available')->default(0);
            $table->integer('used')->default(0);
            $table->integer('total')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique('user_id');
        });

        // Credit transactions
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->integer('amount'); // Positive for additions, negative for deductions
            $table->string('type'); // purchase, usage, bonus, refund, expiry
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'created_at']);
        });

        // AI tools
        Schema::create('ai_tools', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->integer('credits_per_use')->default(1);
            $table->boolean('is_active')->default(true);
            $table->string('required_role')->default('user'); // user, subscriber, premium
            $table->json('config')->nullable();
            $table->timestamps();
        });

        // AI usage logs
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id');
            $table->uuid('ai_tool_id');
            $table->text('input')->nullable();
            $table->longText('output')->nullable();
            $table->integer('credits_used')->default(0);
            $table->string('status')->default('completed'); // pending, processing, completed, failed
            $table->integer('execution_time_ms')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('ai_tool_id')->references('id')->on('ai_tools')->cascadeOnDelete();
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_tools');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credits');
    }
};
