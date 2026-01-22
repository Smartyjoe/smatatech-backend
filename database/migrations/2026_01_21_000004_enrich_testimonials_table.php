<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            if (!Schema::hasColumn('testimonials', 'rating')) {
                $table->tinyInteger('rating')->default(5);
            }
            if (!Schema::hasColumn('testimonials', 'project_type')) {
                $table->string('project_type', 255)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $columns = ['rating', 'project_type'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('testimonials', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
