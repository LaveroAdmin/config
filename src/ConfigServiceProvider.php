<?php

namespace LaveroAdmin\Admin\Config;

use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'lavero-admin-config');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'lavero-admin-config');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        Config::boot();
    }
}
