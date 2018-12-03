<?php

namespace BFF\Cache;


class TaggedMemcache
{
    /**
     * @var Memcache
     */
    private $cache;
    /**
     * @var TagSet
     */
    private $tagset;

    /**
     * @param Memcache $cache
     * @param array $tags
     */
    public function __construct(Memcache $cache, array $tags)
    {
        $this->cache = $cache;
        $this->tagset = new TagSet($cache, $tags);
    }

    /**
     * @param string $key
     * @param $val
     * @param int $ttl
     * @return bool
     */
    public function set(string $key, $val, int $ttl = 0) : bool
    {
        return $this->cache->set($this->taggedItemKey($key), $val, $ttl);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->cache->get($this->taggedItemKey($key));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key) : bool
    {
        return $this->cache->del($this->taggedItemKey($key));
    }

    /**
     * @param string $key
     * @return string
     */
    public function taggedItemKey(string $key) : string
    {
        return sha1($this->tagset->getNamespace() . ':' . $key);
    }

    public function flush() : void
    {
        $this->tagset->reset();
    }
}