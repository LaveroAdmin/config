<?php

namespace LaveroAdmin\Admin\Config;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use LaveroAdmin\Admin\Admin;
use LaveroAdmin\Admin\Extension;

class Config extends Extension
{
    /**
     * Load configure into laravel from database (cached forever until changed).
     *
     * @return void
     */
    public static function load()
    {
        try {
            $all = Cache::rememberForever(ConfigModel::CACHE_KEY, function (): array {
                $model = new ConfigModel();
                $connection = $model->getConnectionName() ?: config('admin.database.connection') ?: config('database.default');
                if (!Schema::connection($connection)->hasTable($model->getTable())) {
                    return [];
                }

                $hasType = Schema::connection($connection)->hasColumn($model->getTable(), 'type');
                $cols = $hasType ? ['name', 'value', 'type'] : ['name', 'value'];

                $result = [];
                foreach (ConfigModel::all($cols) as $config) {
                    $result[$config->name] = $hasType ? $config->getTypedValue() : $config->value;
                }

                return $result;
            });

            if (!empty($all)) {
                config($all);
            }
        } catch (\Throwable $e) {
            // Ignore if database or table doesn't exist yet before migrations run
        }
    }

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('config', __CLASS__);
    }

    /**
     * Register routes for LaveroAdmin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $prefix = config('admin.extensions.config.name', 'config');
            $controller = config('admin.extensions.config.controller', 'LaveroAdmin\Admin\Config\ConfigController');

            $router->post("{$prefix}/save-form", "{$controller}@saveForm")->name('config.save-form');
            $router->get("{$prefix}/builder", "{$controller}@builder")->name('config.builder');
            $router->post("{$prefix}/builder/save-field", "{$controller}@saveField")->name('config.builder.save-field');
            $router->post("{$prefix}/builder/save-order", "{$controller}@saveOrder")->name('config.builder.save-order');
            $router->post("{$prefix}/builder/delete-field", "{$controller}@deleteField")->name('config.builder.delete-field');

            $router->resource($prefix, $controller);
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Config', 'config', 'icon-toggle-on');

        parent::createPermission('Admin Config', 'ext.config', 'config*');
    }
}
