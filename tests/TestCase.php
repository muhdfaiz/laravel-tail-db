<?php

namespace Muhdfaiz\LaravelTailDb\Tests;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Muhdfaiz\LaravelTailDb\TailDatabaseServiceProvider;
use Muhdfaiz\LaravelTailDb\Tests\TestClasses\UserModel;
use Orchestra\Testbench\TestCase as TestBenchTestCase;

abstract class TestCase extends TestBenchTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    /**
     * Setup test environment.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true');

        $app['config']->set('database.default', 'mysql');

        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'database' => env('DB_DATABASE', 'test_db'),
            'port' => env('DB_PORT', '3306'),
            'prefix' => '',
        ]);

        // Clear log file.
        $filename = config('tail-db.filename');
        $path = config('tail-db.path');
        fclose(fopen($path . '/' . $filename,'w'));
    }

    /**
     * Load service provider.
     *
     * @param Application $app
     *
     * @return array|string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            TailDatabaseServiceProvider::class
        ];
    }

    /**
     * Setup dummy database.
     */
    protected function setUpDatabase()
    {
        $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME =  ?";
        $db = DB::select($query, ['test_db']);

        if (empty($db)) {
            DB::select('CREATE DATABASE '. 'test_db');
        }

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Get sql queries with bindings.
     *
     * @param Builder $builder
     *
     * @return string
     */
    public static function getQueriesWithBindings(Builder $builder)
    {
        $addSlashes = str_replace('?', "'?'", $builder->toSql());
        return vsprintf(str_replace('?', '%s', $addSlashes), $builder->getBindings());
    }

    /**
     * Disable the library.
     *
     * @param $app
     */
    protected function useDisableLibrary($app)
    {
        $app->config->set('tail-db.enabled', false);
    }

    /**
     * Enable the library.
     *
     * @param $app
     */
    protected function useEnableLibrary($app)
    {
        $app->config->set('tail-db.enabled', true);
    }

    /**
     * Enable the logging.
     *
     * @param $app
     */
    protected function useEnableLogging($app)
    {
        $app->config->set('tail-db.log_query', true);
    }

    /**
     * Ignore query keyword.
     *
     * @param $app
     */
    protected function useIgnoreQueryKeyword($app)
    {
        $app->config->set('tail-db.ignore_query_keyword', 'select|insert');
    }

    /**
     * Create dummy data.
     *
     * @return mixed
     */
    protected function createDummyData()
    {
        $input = [
            'name' => 'Test User',
            'email' => 'testuser@gmail.com',
        ];

        $user = UserModel::create($input);

        $query = UserModel::where('email', $user->email);
        return $query->first();
    }
}
