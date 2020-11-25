<?php

namespace Muhdfaiz\LaravelTailDb;

use Illuminate\Support\ServiceProvider;

class TailDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                TailDatabaseCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/tail-db.php' => config_path('tail-db.php'),
        ], 'tail-db-config');

        if (config('tail-db.enabled')) {
            $newLoggingChannel = [
                'driver' => 'single',
                'path' => config('tail-db.path') . '/' . config('tail-db.filename'),
                'level' => env('LOG_LEVEL', 'debug'),
            ];

            $this->app['config']["logging.channels.taildb"] = $newLoggingChannel;

            TailDatabase::start($this->app);
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tail-db.php', 'tail-db');
    }
}