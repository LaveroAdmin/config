<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.config.table', 'admin_config');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('label')->nullable();
            $table->string('tab')->nullable();
            $table->string('type', 32)->default('text');
            $table->text('value')->nullable();
            $table->text('options')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort')->default(0);
            $table->timestamps();

            $table->index(['sort', 'id'], 'admin_config_sort_id_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.config.table', 'admin_config');

        Schema::connection($connection)->dropIfExists($table);
    }
}
