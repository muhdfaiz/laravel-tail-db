<?php

namespace Muhdfaiz\LaravelTailDb;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use React\EventLoop\Factory;
use React\Socket\ConnectionInterface;
use React\Socket\Connector;

class DatabaseWatcher
{
    /**
     * Register the watcher.
     *
     * @param  $app
     * @return  void
     */
    public function register($app)
    {
        $app['events']->listen(QueryExecuted::class, [$this, 'recordQuery']);
    }

    /**
     * Record a query was executed.
     *
     * @param QueryExecuted $event
     *
     * @return void
     */
    public function recordQuery(QueryExecuted $event)
    {
        // Check if the query contain keywords user want to ignore based on the config.
        if (config('tail-db.ignore_query_keyword')
            && preg_match('('.config('tail-db.ignore_query_keyword').')', strtolower($event->sql)) === 1
        ) {
            return;
        }

        // Need to skip recording if the query received related to migration.
        // For example, create table, drop table and alter table.
        if ($this->checkIfQueryRelatedToMigration(strtolower($event->sql)) === true) {
            return;
        }

        // Get stack trace.
        $caller = $this->getCallerFromStackTrace();

        // Set data before storing in the database log file.
        $data = [
            'url' => Request::url(),
            'connection' => $event->connectionName,
            'bindings' => $event->bindings,
            'sql' => strtolower($this->replaceBindings($event)),
            'time' => number_format($event->time, 2, '.', ''),
            'file' => $caller['file'],
            'line' => $caller['line'],
        ];

        // Store the query data in the log file if enabled.
        if (config('tail-db.log_query')) {
            Log::channel('taildb')->info(json_encode($data));
        }

        $this->sendDataToReactPHPServer($data);
    }

    /**
     * Send data to react PHP socket.
     *
     * @param array $data SQL query data.
     *
     * @return void
     */
    protected function sendDataToReactPHPServer(array $data)
    {
        $host = config('tail-db.host');
        $port = config('tail-db.port');

        $loop = Factory::create();
        $connector = new Connector($loop);

        $connector->connect($host.':'.$port)
            ->then(function (ConnectionInterface $connection) use ($data) {
                $connection->write(json_encode($data));
            });

        $loop->run();
    }

    /**
     * Find the first frame in the stack trace outside of Laravel vendor.
     *
     * @return array
     */
    protected function getCallerFromStackTrace()
    {
        $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))->forget(0);
        return $trace->first(function ($frame) {
            if (! isset($frame['file'])) {
                return false;
            }

            if (Str::contains($frame['file'], 'vendor'.DIRECTORY_SEPARATOR.'laravel')) {
                return false;
            }

            return $frame['file'];
        });
    }

    /**
     * Format the given bindings to strings.
     *
     * @param QueryExecuted $event
     *
     * @return array
     */
    protected function formatBindings($event)
    {
        return $event->connection->prepareBindings($event->bindings);
    }

    /**
     * Replace the placeholders with the actual bindings.
     *
     * @param QueryExecuted $event
     *
     * @return string
     */
    public function replaceBindings(QueryExecuted $event)
    {
        $sql = $event->sql;

        foreach ($this->formatBindings($event) as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

            if ($binding === null) {
                $binding = 'null';
            } elseif (! is_int($binding) && ! is_float($binding)) {
                $binding = $event->connection->getPdo()->quote($binding);
            }

            $sql = preg_replace($regex, $binding, $sql, 1);
        }

        return $sql;
    }

    /**
     * Check if query related to migration.
     *
     * @param $query
     *
     * @return bool
     */
    private function checkIfQueryRelatedToMigration($query)
    {
        $query = strtolower($query);

        // Get ignore query from the config in case user want to ignore other query.


        // Default query that will skip by Laravel Tail DB.
        if (strpos($query, 'explain') !== false || strpos($query, 'alter table') !== false
            || strpos($query, 'create table') !== false || strpos($query, 'drop table') !== false
            || strpos($query, 'create index') !== false || strpos($query, 'create unique index') !== false
            || strpos($query, 'information_schema') !== false
        ) {
            return true;
        }

        return false;
    }
}
