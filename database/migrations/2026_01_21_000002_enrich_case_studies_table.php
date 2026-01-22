<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('case_studies', 'short_description')) {
                $table->string('short_description', 255)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'duration')) {
                $table->string('duration', 100)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'year')) {
                $table->string('year', 4)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'challenge_overview')) {
                $table->text('challenge_overview')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'challenge_points')) {
                $table->json('challenge_points')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'solution_overview')) {
                $table->text('solution_overview')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'solution_points')) {
                $table->json('solution_points')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'results_data')) {
                $table->json('results_data')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'process_steps')) {
                $table->json('process_steps')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'testimonial_quote')) {
                $table->text('testimonial_quote')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'testimonial_author')) {
                $table->string('testimonial_author', 255)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'testimonial_role')) {
                $table->string('testimonial_role', 255)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'gallery')) {
                $table->json('gallery')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'meta_title')) {
                $table->string('meta_title', 255)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'meta_description')) {
                $table->text('meta_description')->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'highlight_stat_value')) {
                $table->string('highlight_stat_value', 50)->nullable();
            }
            if (!Schema::hasColumn('case_studies', 'highlight_stat_label')) {
                $table->string('highlight_stat_label', 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('case_studies', function (Blueprint $table) {
            $columns = [
                'short_description', 'duration', 'year', 'challenge_overview', 'challenge_points',
                'solution_overview', 'solution_points', 'results_data', 'process_steps',
                'testimonial_quote', 'testimonial_author', 'testimonial_role', 'gallery',
                'meta_title', 'meta_description', 'highlight_stat_value', 'highlight_stat_label'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('case_studies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
