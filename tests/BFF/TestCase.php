<?php

namespace BFF\Test;


use BFF\Config;
use BFF\Registry;
use BFF\Service;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $config = new Config(Config::BASE_CONFIG_ENV, __DIR__ . '/Config');
        Registry::set(Service::CONFIG, $config);
    }
}