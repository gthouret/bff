<?php

namespace BFF\Test\Db\Adapter;

use BFF\Db\Adapter\PdoFactory;
use BFF\Services;
use BFF\Test\TestCase;

class PdoFactoryTest extends TestCase
{
    public function testPdoFactoryDefault()
    {
        $pdo = PdoFactory::make(Services::config());
        $this->assertInstanceOf('PDO', $pdo);
    }
}