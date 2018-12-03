<?php

namespace BFF\Test\Cache;

use BFF\Services;
use BFF\Cache\TaggedMemcache;
use BFF\Test\TestCase;

class TaggedMemcacheTest extends TestCase
{
    public function testTaggedItemKey()
    {
        $tags = [
            'tag1',
            'tag2'
        ];

        $taggedCache = new TaggedMemcache(Services::cache(), $tags);
        $this->assertEquals(40, strlen($taggedCache->taggedItemKey('key1')));
    }

    public function testSetGetDel()
    {
        $tags = [
            'tag1',
            'tag2'
        ];

        $key = 'something';
        $value = 'hello there';

        $taggedCache = new TaggedMemcache(Services::cache(), $tags);

        $this->assertTrue($taggedCache->set($key, $value));
        $this->assertEquals($value, $taggedCache->get($key));
        $this->assertTrue($taggedCache->del($key));
        $this->assertFalse($taggedCache->get($key));
    }

    public function testDel()
    {
        $tags = [
            'tag1',
            'tag2'
        ];

        $key = 'something';
        $value = 'hello there';

        $taggedCache = new TaggedMemcache(Services::cache(), $tags);

        $this->assertTrue($taggedCache->set($key, $value));
        $this->assertEquals($value, $taggedCache->get($key));
    }

    public function testReset()
    {
        $key = 'something';
        $value = 'hello there';

        $taggedCache1 = new TaggedMemcache(Services::cache(), ['key1']);
        $taggedCache2 = new TaggedMemcache(Services::cache(), ['key2']);
        $taggedCache3 = new TaggedMemcache(Services::cache(), ['key1', 'key2']);

        $this->assertTrue($taggedCache1->set($key, $value));
        $this->assertEquals($value, $taggedCache1->get($key));
        $this->assertTrue($taggedCache2->set($key, $value));
        $this->assertEquals($value, $taggedCache2->get($key));
        $this->assertTrue($taggedCache3->set($key, $value));
        $this->assertEquals($value, $taggedCache3->get($key));

        $taggedCache1->flush();

        $this->assertFalse($taggedCache1->get($key));
        $this->assertEquals($value, $taggedCache2->get($key));
        $this->assertFalse($taggedCache3->get($key));
    }
}