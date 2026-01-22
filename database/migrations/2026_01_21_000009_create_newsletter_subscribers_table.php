<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('email')->unique();
                $table->boolean('consent')->default(false);
                $table->string('status')->default('active'); // active, unsubscribed
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('subscribed_at')->nullable();
                $table->timestamp('unsubscribed_at')->nullable();
                $table->timestamps();

                $table->index('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscribers');
    }
};
