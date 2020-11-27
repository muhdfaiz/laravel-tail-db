<?php

namespace Muhdfaiz\LaravelTailDb;

class TailDatabase
{
    /**
     * Register the database watchers.
     *
     * @param  $app
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
     * Used this watcher to listen for new SQL query executed in the application..
     *
     * @param $app
     */
    protected static function registerDatabaseWatcher($app)
    {
        $databaseWatcher = $app->make(DatabaseWatcher::class);

        $databaseWatcher->register($app);
    }
}
