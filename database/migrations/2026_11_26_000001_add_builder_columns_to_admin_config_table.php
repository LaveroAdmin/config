<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $tableName = config('admin.extensions.config.table', 'admin_config');

        if (!Schema::connection($connection)->hasTable($tableName)) {
            return;
        }

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (!Schema::connection($connection)->hasColumn($tableName, 'label')) {
                $table->string('label')->nullable()->after('name');
            }
            if (!Schema::connection($connection)->hasColumn($tableName, 'tab')) {
                $table->string('tab')->nullable()->after('label');
            }
            if (!Schema::connection($connection)->hasColumn($tableName, 'type')) {
                $table->string('type', 32)->default('text')->after('tab');
            }
            if (!Schema::connection($connection)->hasColumn($tableName, 'options')) {
                $table->text('options')->nullable()->after('value');
            }
            if (!Schema::connection($connection)->hasColumn($tableName, 'sort')) {
                $table->integer('sort')->default(0)->after('description');
                $table->index(['sort', 'id'], 'admin_config_sort_id_index');
            }
        });
    }

    public function down(): void
    {
        $connection = config('admin.database.connection') ?: config('database.default');
        $tableName = config('admin.extensions.config.table', 'admin_config');

        if (!Schema::connection($connection)->hasTable($tableName)) {
            return;
        }

        $drop = [];
        foreach (['label', 'tab', 'type', 'options', 'sort'] as $col) {
            if (Schema::connection($connection)->hasColumn($tableName, $col)) {
                $drop[] = $col;
            }
        }

        if (!empty($drop)) {
            Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($drop) {
                $table->dropColumn($drop);
            });
        }
    }
};
