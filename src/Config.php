<?php

namespace LaveroAdmin\Admin\Config;

use LaveroAdmin\Admin\Admin;
use LaveroAdmin\Admin\Extension;

class Config extends Extension
{
    /**
     * Load configure into laravel from database.
     *
     * @return void
     */
    public static function load()
    {
        try {
            $model = new ConfigModel();
            $connection = $model->getConnectionName() ?: config('admin.database.connection') ?: config('database.default');
            if (\Illuminate\Support\Facades\Schema::connection($connection)->hasTable($model->getTable())) {
                foreach (ConfigModel::all(['name', 'value']) as $config) {
                    config([$config['name'] => $config['value']]);
                }
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
            $router->resource(
                config('admin.extensions.config.name', 'config'),
                config('admin.extensions.config.controller', 'LaveroAdmin\Admin\Config\ConfigController')
            );
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
