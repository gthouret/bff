<?php

namespace BFF\Test;

use BFF\Logger;
use BFF\Services;

class LoggerTest extends TestCase
{
    private $queue;

    public function setUp()
    {
        parent::setUp();
        $this->queue = Services::queue();
        $this->queue->del('log_app');
    }

    public function testLogApp()
    {
        $message = 'Something Bad Happened';

        Logger::logApp('Test', Logger::LEVEL_ERROR, $message);

        $lastLogApp = $this->queue->rpop('log_app');
        $this->assertContains('["Test","[ERROR]","Something Bad Happened"]]', $lastLogApp);
    }
}