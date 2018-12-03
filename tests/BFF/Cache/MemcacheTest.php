<?php

namespace BFF\Test\Cache;

use BFF\Cache\Memcache;
use BFF\Services;
use BFF\Test\TestCase;

class MemcacheTest extends TestCase
{
    /**
     * @var Memcache
     */
    private $cache;

    public function setUp()
    {
        parent::setUp();
        $config = Services::config();
        $this->cache = new Memcache($config->get('memcache'));
    }

    public function testSetGetDel()
    {
        $key = 'test' . rand(0,100);
        $value = 'hello there!';
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        sleep($ttl+1);
        $this->assertFalse($this->cache->get($key));

        $this->cache->set($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->del($key);
        $this->assertFalse($this->cache->get($key));
    }

    public function testSetGetArray()
    {
        $key = 'test' . rand(0,100);
        $value = [1 => 'hello', 2 => 'there!'];
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $cacheVal = $this->cache->get($key);
        $this->assertEquals($value, $cacheVal);
        $this->assertTrue(is_array($cacheVal));
        $this->cache->del($key);
        $this->assertFalse($this->cache->get($key));
    }

    public function testSetGetObj()
    {
        $key = 'test' . rand(0,100);
        $obj = new \StdClass();
        $obj->exhibitA = 'hello';
        $obj->exhibitB = 'there!';
        $value = $obj;
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $cacheVal = $this->cache->get($key);
        $this->assertEquals($value, $cacheVal);
        $this->assertTrue(is_object($cacheVal));
        $this->cache->del($key);
        $this->assertFalse($this->cache->get($key));
    }

    public function testAdd()
    {
        $key = 'test' . rand(0,100);
        $value = 'hello there!';
        $value2 = 'why not sir?';
        $ttl = 2;

        $this->cache->add($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->add($key, $value2, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->del($key);
        $this->assertFalse($this->cache->get($key));
    }

    public function testGetMulti()
    {
        $key = 'test' . rand(0,100);
        $key2 = 'test' . rand(0,100);
        $value = 'hello there!';
        $value2 = 'why not sir?';
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->set($key2, $value2, $ttl);
        $this->assertEquals($value2, $this->cache->get($key2));

        $multi = $this->cache->getMulti([$key, $key2]);
        $this->assertTrue(is_array($multi));
        $this->assertEquals($value, $multi[$key]);
        $this->assertEquals($value2, $multi[$key2]);

        $this->cache->del($key);
        $this->assertFalse($this->cache->get($key));
        $this->cache->del($key2);
        $this->assertFalse($this->cache->get($key2));
    }

    public function testTouch()
    {
        $key = 'test' . rand(0,100);
        $value = 'hello there!';
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));
        sleep(1);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->touch($key, 5);
        sleep(2);
        $this->assertEquals($value, $this->cache->get($key));
        $this->cache->touch($key, 5);
        sleep(2);
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testIncDec() {
        $key = 'test' . rand(0,100);
        $value = 194;
        $ttl = 2;

        $this->cache->set($key, $value, $ttl);
        $this->assertEquals($value, $this->cache->get($key));

        $this->cache->inc($key);
        $value++;
        $this->assertEquals($value, $this->cache->get($key));

        $this->cache->inc($key, 23);
        $value += 23;
        $this->assertEquals($value, $this->cache->get($key));

        $this->cache->dec($key);
        $value--;
        $this->assertEquals($value, $this->cache->get($key));

        $this->cache->dec($key, 12);
        $value -= 12;
        $this->assertEquals($value, $this->cache->get($key));
    }
}