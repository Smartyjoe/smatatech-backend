<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'source_url')) {
                $table->string('source_url', 500)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'referrer')) {
                $table->string('referrer', 500)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'utm_source')) {
                $table->string('utm_source', 255)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'utm_medium')) {
                $table->string('utm_medium', 255)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'utm_campaign')) {
                $table->string('utm_campaign', 255)->nullable();
            }
            if (!Schema::hasColumn('contacts', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $columns = ['source_url', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign', 'ip_address'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('contacts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
