<?php

namespace BFF\Test\Db\Adapter;

use BFF\Db\Adapter\PdoFactory;
use BFF\Service;
use BFF\Test\TestCase;

class PdoFactoryTest extends TestCase
{
    public function testPdoFactoryDefault()
    {
        $pdo = PdoFactory::make(Service::config());
        $this->assertInstanceOf('PDO', $pdo);
    }
}