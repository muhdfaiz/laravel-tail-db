<?php

namespace Muhdfaiz\LaravelTailDb\Tests;

class TailDatabaseServiceProviderTest extends TestCase
{
    /**
     * @test
     * @environment-setup useEnableLogging
     */
    public function it_will_add_taildb_logging_channel_when_enabled()
    {
        $tailDBLoggingChannel = config('logging.channels.taildb');

        $this->assertNotEmpty($tailDBLoggingChannel);
    }

    /**
     * @test
     * @environment-setup useDisableLibrary
     */
    public function it_will_not_add_taildb_logging_channel_when_disabled()
    {
        $tailDBLoggingChannel = config('logging.channels.taildb');

        $this->assertEmpty($tailDBLoggingChannel);
    }
}