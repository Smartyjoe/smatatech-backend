<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('AI Assistant');
            $table->text('greeting_message')->nullable();
            $table->text('initial_message')->nullable();
            $table->text('fallback_message')->nullable();
            $table->string('personality_tone')->default('professional');
            $table->text('system_prompt')->nullable();
            $table->json('allowed_topics')->nullable();
            $table->json('restricted_topics')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_configs');
    }
};
