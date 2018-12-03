<?php

namespace BFF\Test\Queue\Backend;

use BFF\Queue\Backend\Redis;
use BFF\Services;
use BFF\Test\TestCase;

class RedisTest extends TestCase
{
    /**
     * @var Redis
     */
    private $redis;

    /**
     * @var array
     */
    private $keysToRemove = [];

    public function setUp()
    {
        parent::setUp();
        $this->redis = Services::queue();
    }

    public function tearDown()
    {
        foreach ($this->keysToRemove as $key) {
            $this->redis->del($key);
        }
    }

    public function testOperation()
    {
        $this->keysToRemove[] = 'test1';

        $this->redis->lpush('test1', 'hello');
        $value = $this->redis->rpop('test1');

        $this->assertEquals('hello', $value);
    }

    public function testDel()
    {
        $key = 'key_' . mt_rand(0,100);
        $this->keysToRemove[] = $key;

        for ($i=0; $i<5; $i++) {
            $this->redis->lpush($key, 'hello');
        }

        $this->assertEquals(5, $this->redis->llen($key));
        $this->assertEquals(1, $this->redis->del($key));
        $this->assertEquals(0, $this->redis->llen($key));
    }

    public function testLrem()
    {
        $key = 'key_' . mt_rand(0,100);
        $this->keysToRemove[] = $key;

        for ($i=0; $i<5; $i++) {
            $this->redis->lpush($key, 'hello');
        }

        $this->assertEquals(5, $this->redis->llen($key));
        $this->assertEquals(1, $this->redis->lrem($key, 'hello'));
        $this->assertEquals(1, $this->redis->lrem($key, 'hello'));
        $this->assertEquals(3, $this->redis->llen($key));
    }

    public function testBlpop()
    {
        $key = 'key_' . mt_rand(0,100);
        $this->keysToRemove[] = $key;

        $this->redis->lpush($key, 'hi');
        $this->redis->lpush($key, 'hello');
        $this->redis->lpush($key, 'howdee');

        $last = $this->redis->blpop($key);
        $this->assertEquals('howdee', $last[1]);
    }
}