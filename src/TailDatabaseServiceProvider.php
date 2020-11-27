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

        // Check if Laravel Tail Db enable or not.
        // If not enable listen to the sql query from application.
        if (config('tail-db.enabled')) {
            // Check if Laravel Tail Db logging enable or not.
            // If enable store the query in log file.
            if (config('tail-db.log_query')) {
                $newLoggingChannel = [
                    'driver' => 'single',
                    'path' => config('tail-db.path') . '/' . config('tail-db.filename'),
                    'level' => env('LOG_LEVEL', 'debug'),
                ];

                $this->app['config']["logging.channels.taildb"] = $newLoggingChannel;
            }

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
