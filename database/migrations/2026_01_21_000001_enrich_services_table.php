<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'features')) {
                $table->json('features')->nullable();
            }
            if (!Schema::hasColumn('services', 'benefits')) {
                $table->json('benefits')->nullable();
            }
            if (!Schema::hasColumn('services', 'process_steps')) {
                $table->json('process_steps')->nullable();
            }
            if (!Schema::hasColumn('services', 'meta_title')) {
                $table->string('meta_title', 255)->nullable();
            }
            if (!Schema::hasColumn('services', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('services', 'og_image')) {
                $table->string('og_image', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $columns = ['features', 'benefits', 'process_steps', 'meta_title', 'meta_description', 'og_image'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
