<?php

namespace Muhdfaiz\LaravelTailDb\Tests;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Muhdfaiz\LaravelTailDb\Tests\TestClasses\UserModel;
use Symfony\Component\Process\Process;

class DatabaseWatcherTest extends TestCase
{
    /**
     * @test
     * @environment-setup useEnableLibrary
     */
    public function it_will_registered_query_executed_event_when_enabled()
    {
        $listeners = $this->app['events']->getListeners(QueryExecuted::class);

        $this->assertNotEmpty($listeners);
    }

    /**
     * @test
     * @environment-setup useDisableLibrary
     */
    public function it_will_not_registered_query_executed_event_when_disabled()
    {
        $listeners = $this->app['events']->getListeners(QueryExecuted::class);

        $this->assertEmpty($listeners);
    }

    /**
     * @test
     * @environment-setup useEnableLogging
     */
    public function it_record_the_query_to_the_log_file_if_enabled()
    {
        $user = UserModel::create(['name' => 'Test Person', 'email' => 'testuser@gmail.com']);

        $query = UserModel::where('email', $user->email);
        $user = $query->first();

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        // Read last line of the log file.
        $process = new Process(['tail', '-n', '1', $path . '/' . $filename]);
        $process->run();
        $logContent = $process->getOutput();

        // Parse the data.
        $needle = 'testing.INFO: ';
        $databaseQueryJSON = str_replace($needle, '', strstr($logContent, $needle));
        $databaseQueryArray = json_decode($databaseQueryJSON, true);

        $this->assertEquals($user->email, $databaseQueryArray['bindings'][0]);
        $this->assertEquals($this->getQueriesWithBindings($query), $databaseQueryArray['sql']);

        $reflector = new \ReflectionClass($this);
        $filename = $reflector->getFileName();

        $this->assertEquals($filename, $databaseQueryArray['file']);
    }

    /**
     * @test
     */
    public function it_will_not_record_the_query_to_the_log_file_if_disabled()
    {
        $user = UserModel::create(['name' => 'Test Person', 'email' => 'testuser@gmail.com']);

        $query = UserModel::where('email', $user->email);
        $user = $query->first();

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        // Read last line of the log file.
        $process = new Process(['tail', '-n', '1', $path . '/' . $filename]);
        $process->run();
        $logContent = $process->getOutput();

        $this->assertEmpty($logContent);
    }

    /**
     * @test
     * @environment-setup useEnableLogging
     */
    public function it_will_skip_recording_query_related_with_migration()
    {
        $this->createDummyData();

        DB::select('explain select * from users');

        Schema::dropIfExists('users');

        Schema::dropIfExists('test_table');
        Schema::create('test_table', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
        });

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        $logData = file_get_contents($path . '/' . $filename);

        $this->assertFalse(str_contains($logData, 'drop table'));
        $this->assertFalse(str_contains($logData, 'create table'));
        $this->assertFalse(str_contains($logData, 'alter table'));
        $this->assertFalse(str_contains($logData, "create table `test_table`"));
    }

    /**
     * @test
     * @environment-setup useEnableLogging
     */
    public function it_will_not_record_query_contains_ignore_keyword()
    {
        config(['tail-db.ignore_query_keyword' => 'select|insert']);

        $this->createDummyData();

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        $logData = file_get_contents($path . '/' . $filename);

        $this->assertEmpty($logData);
    }
}