<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->string('author_name');
            $table->string('author_email');
            $table->text('content');
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');
            $table->timestamps();

            $table->index('post_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
    }
};
