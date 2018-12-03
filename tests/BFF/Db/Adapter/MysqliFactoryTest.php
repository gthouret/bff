<?php

namespace BFF\Db\Adapter;

use BFF\Services;
use BFF\Test\TestCase;

class MysqliFactoryTest extends TestCase
{
    public function testMake()
    {
        $dbi = MysqliFactory::make(Services::config());
        $this->assertInstanceOf('mysqli', $dbi);
    }
}