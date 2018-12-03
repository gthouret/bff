<?php

namespace BFF\Test\Db;

use BFF\Db\PdoDsn;
use BFF\Services;
use BFF\Test\TestCase;

class PdoDsnTest extends TestCase
{
    public function testFromConfig()
    {
        $config = Services::config();
        $dsn = PdoDsn::fromConfig($config->get('db'));

        $expectedDsn = 'mysql:dbname=bff-test;host=127.0.0.1';
        $this->assertEquals($expectedDsn, $dsn);
    }
}