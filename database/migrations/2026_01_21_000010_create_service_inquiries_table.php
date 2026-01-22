<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_inquiries')) {
            Schema::create('service_inquiries', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('service_id')->nullable();
                $table->string('service_slug')->nullable();
                $table->string('name');
                $table->string('email');
                $table->string('phone', 50)->nullable();
                $table->string('company')->nullable();
                $table->string('budget_range', 50)->nullable();
                $table->string('timeline', 50)->nullable();
                $table->text('message');
                $table->string('status')->default('new'); // new, contacted, converted, closed
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index('service_id');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('service_inquiries');
    }
};
