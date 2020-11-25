<?php

namespace Muhdfaiz\LaravelTailDb\Tests;

use Illuminate\Support\Facades\Artisan;

class TailDatabaseCommandTest extends TestCase
{
    /** @test */
    public function it_will_not_run_if_disabled()
    {
        config(['tail-db.enabled' => false]);

        $expectOutput = ' Laravel Tail DB has been disabled. Please enabled first before running the command. ';

        $this->artisan('tail:db')->expectsOutput($expectOutput);
    }

    /** @test */
    public function it_will_run_if_enabled()
    {
        config(['tail-db.enabled' => true]);
        config(['tail-db.show_explain' => true]);

        $this->createDummyData();

        Artisan::call('tail:db');
        $output = Artisan::output();

        $this->assertTrue(str_contains($output, 'select_type'));
        $this->assertTrue(str_contains($output, 'Extra'));
    }

    /** @test */
    public function it_will_not_execute_explain_query_if_show_explain_config_false()
    {
        config(['tail-db.enabled' => true]);
        config(['tail-db.show_explain' => false]);

        $this->createDummyData();

        Artisan::call('tail:db');
        $output = Artisan::output();

        $this->assertFalse(str_contains($output, 'select_type'));
        $this->assertFalse(str_contains($output, 'Extra'));
    }

    /** @test */
    public function it_will_clear_log_if_show_clear_log_config_true()
    {
        config(['tail-db.enabled' => true]);
        config(['tail-db.clear_log' => true]);

        $user = $this->createDummyData();

        Artisan::call('tail:db');

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        $logData = file_get_contents($path . '/' . $filename);
        $this->assertEmpty($logData);
    }

    /** @test */
    public function it_will_not_clear_log_if_show_clear_log_config_false()
    {
        config(['tail-db.enabled' => true]);
        config(['tail-db.clear_log' => false]);

        $this->createDummyData();

        Artisan::call('tail:db');

        $filename = config('tail-db.filename');
        $path = config('tail-db.path');

        $logData = file_get_contents($path . '/' . $filename);

        $this->assertNotEmpty($logData);
    }
}
