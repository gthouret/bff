<?php

/**
 * Based on Laravel TagSet Implementation
 * https://github.com/laravel/framework/blob/5.6/src/Illuminate/Cache/TagSet.php
 */

namespace BFF\Cache;

class TagSet
{
    /**
     * @var Memcache
     */
    private $cache;
    /**
     * @var String[]
     */
    private $tags;

    public function __construct(Memcache $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tags = $tags;
    }

    public function reset()
    {
        array_walk($this->tags, [$this, 'resetTag']);
    }

    public function resetTag(string $tag) : string
    {
        $id = str_replace('.', '', uniqid('', true));

        $this->cache->set($this->tagKey($tag), $id);
        return $id;
    }

    public function getNamespace() : string
    {
        return implode('|', $this->tagIds());
    }

    protected function tagIds() : array
    {
        return array_map([$this, 'tagId'], $this->tags);
    }

    public function tagId(string $tag) : string
    {
        return $this->cache->get($this->tagKey($tag)) ?: $this->resetTag($tag);
    }

    public function tagKey(string $tag) : string
    {
        return "tag:$tag:key";
    }

    public function getTags() : array
    {
        return $this->tags;
    }
}