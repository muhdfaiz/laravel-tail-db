<?php

namespace Muhdfaiz\LaravelTailDb;

use Illuminate\Foundation\Application;

class TailDatabase
{
    /**
     * Register the database watchers and start the logging if enabled.
     *
     * @param  Application  $app
     * @return void
     */
    public static function start($app)
    {
        if (! config('tail-db.enabled')) {
            return;
        }

        static::registerDatabaseWatcher($app);
    }

    /**
     * Register database watcher.
     * Used this watcher to store database query.
     *
     * @param Application $app
     */
    protected static function registerDatabaseWatcher(Application $app)
    {
        $databaseWatcher = $app->make(DatabaseWatcher::class);

        $databaseWatcher->register($app);
    }
}
