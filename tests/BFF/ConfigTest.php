<?php

namespace BFF\Test;

use BFF\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testBase()
    {
        $config = new Config(Config::BASE_CONFIG_ENV, __DIR__ . '/Config');
        $this->assertEquals(1, $config->get('nodeId'));
    }

    public function testEnv()
    {
        $config = new Config('development', __DIR__ . '/Config');
        $this->assertEquals($config->get('nodeId'), 1);
        $pathsConfig = $config->get('paths');
        $this->assertEquals('/tmp', $pathsConfig['export']);
    }

    public function testArrayAccess()
    {
        $config = new Config(Config::BASE_CONFIG_ENV, __DIR__ . '/Config');

        $this->assertEquals($config->get('nodeId'), $config['nodeId']);
        $pathConfig = $config->get('paths');
        $this->assertEquals($pathConfig['export'], $config['paths']['export']);

        $this->assertTrue(isset($config['paths']['export']));

        $config['paths'] = 'hello';
        $this->assertEquals('hello', $config['paths']);

        unset($config['paths']);
        $this->assertFalse(isset($config['paths']));
    }

    public function testInvalidKey()
    {
        $config = new Config(Config::BASE_CONFIG_ENV, __DIR__ . '/Config');
        $this->expectException('BFF\Config\Exception');
        $this->expectExceptionMessage('Config key test not found');
        $config->get('test');
    }
}