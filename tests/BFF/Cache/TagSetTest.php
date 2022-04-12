<?php

namespace BFF\Test;

use BFF\Cache\Memcache;
use BFF\Cache\TagSet;
use BFF\Service;

class TagSetTest extends TestCase
{
    private $tags = [
        'offer_tag',
        'offer_216342'
    ];

    /**
     * @var Memcache
     */
    private $cache;

    public function setUp()
    {
        parent::setUp();
        $this->cache = Service::cache();
    }

    public function testGetTags()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $this->assertEquals($this->tags, $tagset->getTags());
    }

    public function testTagKey()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $this->assertEquals('tag:offer_216342:key', $tagset->tagKey('offer_216342'));
    }

    public function testTagId()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $id = $tagset->tagId($tagset->tagKey('offer_216342'));
        $this->assertEquals(22, strlen($id));
    }

    public function testGetNamespace()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $namespace = $tagset->getNamespace();
        $this->assertEquals(45, strlen($namespace));
    }

    public function testResetTag()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $id1 = $tagset->tagId($tagset->tagKey('offer_216342'));
        $id2 = $tagset->tagId($tagset->tagKey('offer_216342'));
        $tagset->resetTag($tagset->tagKey('offer_216342'));
        $id3 = $tagset->tagId($tagset->tagKey('offer_216342'));

        $this->assertEquals($id1, $id2);
        $this->assertNotEquals($id2, $id3);
    }

    public function reset()
    {
        $tagset = new TagSet($this->cache, $this->tags);
        $id1 = $tagset->tagId($tagset->tagKey('offer_tag'));
        $id2 = $tagset->tagId($tagset->tagKey('offer_216342'));
        $tagset->resetTag($tagset->tagKey('offer_216342'));
        $id3 = $tagset->tagId($tagset->tagKey('offer_tag'));
        $id4 = $tagset->tagId($tagset->tagKey('offer_216342'));

        $this->assertNotEquals($id1, $id3);
        $this->assertNotEquals($id2, $id4);
    }
}