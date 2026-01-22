<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'read_time')) {
                $table->string('read_time', 50)->nullable();
            }
            if (!Schema::hasColumn('posts', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (!Schema::hasColumn('posts', 'tags')) {
                $table->json('tags')->nullable();
            }
            if (!Schema::hasColumn('posts', 'comments_enabled')) {
                $table->boolean('comments_enabled')->default(true);
            }
            if (!Schema::hasColumn('posts', 'og_image')) {
                $table->string('og_image', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $columns = ['read_time', 'is_featured', 'tags', 'comments_enabled', 'og_image'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('posts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
