<?php

namespace BFF\Cache;

use BFF\Patterns\ConfigurableTrait;
use Memcached;

class Memcache
{
    use ConfigurableTrait;

    private $memcached;

    public function __construct(array $config)
    {
        $this->requiredConfigKeys = [
            'host',
            'port',
            'pool'
        ];

        $this->validateConfiguration($config);

        $options = [
            Memcached::OPT_TCP_NODELAY => true,
            Memcached::OPT_BINARY_PROTOCOL => true,
            Memcached::OPT_NO_BLOCK => true,
            Memcached::OPT_COMPRESSION => false,
            Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_IGBINARY
        ];

        $this->memcached = new Memcached($config['pool']);
        if (count($this->memcached->getServerList()) == 0) {
            $this->memcached->setOptions($options);
            $this->memcached->addServer($config['host'], $config['port']);
        }
    }

    public function add(string $key, $val, int $ttl = 0) : bool
    {
        return $this->memcached->add($key, $val, $ttl);
    }

    public function set(string $key, $val, int $ttl = 0) : bool
    {
        return $this->memcached->set($key, $val, $ttl);
    }

    public function get(string $key)
    {
        return $this->memcached->get($key);
    }

    public function getMulti(array $keys)
    {
        return $this->memcached->getMulti($keys);
    }

    public function del(string $key) : bool
    {
        return $this->memcached->delete($key);
    }

    public function touch(string $key, int $ttl = 0) : bool
    {
        return $this->memcached->touch($key, $ttl);
    }

    public function inc(string $key, int $offset = 1, int $initial = 0, int $expiry = 0)
    {
        return $this->memcached->increment($key, $offset, $initial, $expiry);
    }

    public function dec(string $key, int $offset = 1, int $initial = 0, int $expiry = 0)
    {
        return $this->memcached->decrement($key, $offset, $initial, $expiry);
    }

    public function setEarlyExpire(string $key, $val, int $ttlMin = 0, int $ttlMax = 0) : bool
    {
        $now = time();
        $earlyExpiry = $now + $ttlMin;
        $expiry = $now + $ttlMax;

        $payload = [$earlyExpiry, $expiry, $val];

        return $this->memcached->set($key, $payload, $ttlMax);
    }

    public function getEarlyExpire(string $key)
    {
        $payload = $this->memcached->get($key);

        if (!$payload)
            return false;

        list ($earlyExpiry, $expiry, $val) = $payload;

        $now = time();
        $effectiveExpiry = mt_rand($earlyExpiry, $expiry);

        return ($now > $effectiveExpiry) ? false : $val;
    }

    public function flush() : bool
    {
        return $this->memcached->flush();
    }
}
