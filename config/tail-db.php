<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Laravel Tail Database Switch
    |--------------------------------------------------------------------------
    |
    | This option used to enable or disable the Laravel Tail DB watcher.
    | If enabled, every sql query executed from the application will
    | be captured and store in the directory you based on the config.
    |
    */
    'enabled' => env('TAIL_DB_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Duration of time considered slow query.
    |--------------------------------------------------------------------------
    |
    | This option used to tell Laravel Tail DB if the query slow or not.
    | For example, if you specify 2000ms and the last query executed take
    | more than 2000ms, Laravel Tail DB will highlight the time with red color.
    | If the query below than 2000ms Laravel Tail DB will highlight with green color.
    | The value must be in milliseconds.
    */
    'slow_duration' => env('TAIL_DB_SLOW_DURATION', 3000),

    /*
    |--------------------------------------------------------------------------
    | Ignore queries
    |--------------------------------------------------------------------------
    |
    | You can specify the keyword to skip the tailing.
    | Laravel Tail DB will check if the query contain those keyword or not.
    | If exist, Laravel Tail DB will skip recording to log file.
    | The key keyword must be separated by comma.
    | Example: alter table}drop table. (Separated by |)
    |
    */
    'ignore_query_keyword' => env('TAIL_DB_IGNORE_QUERY_KEYWORD', ''),

    /*
    |--------------------------------------------------------------------------
    | Filename to store mysql queries log
    |--------------------------------------------------------------------------
    |
    | Default filename is database.log
    |
    */
    'filename' => env ('TAIL_DB_FILENAME', 'database.log'),

    /*
    |--------------------------------------------------------------------------
    | Path to store sql queries log.
    |--------------------------------------------------------------------------
    |
    | Default path is inside storage/logs.
    |
    */
    'path' => env ('TAIL_DB_PATH', storage_path('logs')),

    /*
    |--------------------------------------------------------------------------
    | Show explain sql during the tail.
    |--------------------------------------------------------------------------
    |
    | By default every sql query executed, laravel tail db will run explain
    | command. Useful if you want to troubleshooting performance issue.
    | If turn off, Laravel Tail DB only show the query executed, the time and
    | the location where the query executed.
    */
    'show_explain' => env ('TAIL_DB_SHOW_EXPLAIN', true),

    /*
    |--------------------------------------------------------------------------
    | Clear log
    |--------------------------------------------------------------------------
    |
    | When you end the tail:database command or every time Laravel Tail DB
    | received new data, the data in the log will be cleared.
    */
    'clear_log' => env ('TAIL_DB_CLEAR_LOG', true),
];
