<?php

namespace BFF\Db\Adapter;

use BFF\Service;
use BFF\Test\TestCase;

class MysqliFactoryTest extends TestCase
{
    public function testMake()
    {
        $dbi = MysqliFactory::make(Service::config());
        $this->assertInstanceOf('mysqli', $dbi);
    }
}