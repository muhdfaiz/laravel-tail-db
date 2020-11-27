<?php

namespace Muhdfaiz\LaravelTailDb\Tests;

class TailDatabaseCommandTest extends TestCase
{
    /** @test */
    public function it_will_not_run_if_disabled()
    {
        config(['tail-db.enabled' => false]);

        $expectOutput = ' Laravel Tail DB has been disabled. Please enabled first before running the command. ';

        $this->artisan('tail:db')->expectsOutput($expectOutput);
    }
}
