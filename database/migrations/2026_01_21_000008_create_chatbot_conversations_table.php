<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chatbot_conversations')) {
            Schema::create('chatbot_conversations', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('session_id', 255)->index();
                $table->uuid('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->json('messages');
                $table->timestamps();

                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
