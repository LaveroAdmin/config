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
        $connection = config('admin.database.connection') ?: config('database.default');
        $table = config('admin.extensions.config.table', 'admin_config');

        if (Schema::connection($connection)->hasTable($table) && Schema::connection($connection)->hasColumn($table, 'value')) {
            Schema::connection($connection)->table($table, function (Blueprint $table) {
                $table->text('value')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $table = config('admin.extensions.config.table', 'admin_config');

        if (Schema::connection($connection)->hasTable($table) && Schema::connection($connection)->hasColumn($table, 'value')) {
            Schema::connection($connection)->table($table, function (Blueprint $table) {
                $table->string('value')->nullable()->change();
            });
        }
    }
};
