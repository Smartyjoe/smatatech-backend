<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chatbot_config', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('system_prompt')->nullable();
            $table->string('personality_tone')->default('professional'); // professional, friendly, casual, formal, technical
            $table->json('allowed_topics')->nullable();
            $table->json('restricted_topics')->nullable();
            $table->text('greeting_message')->nullable();
            $table->text('fallback_message')->nullable();
            $table->boolean('is_enabled')->default(true);
            $table->string('version_label')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_config');
    }
};
