<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();
            $table->string('phone')->nullable();
            $table->string('project_type')->nullable();
            $table->string('budget')->nullable();
            $table->json('services')->nullable();
            $table->longText('message');
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->index('read');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
