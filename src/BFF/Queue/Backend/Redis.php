<?php

namespace BFF\Queue\Backend;

use BFF\Patterns\ConfigurableTrait;


class Redis
{
    use ConfigurableTrait;


    /**
     * @var \Redis
     */
    private $redis;
    /**
     * @var array
     */
    private $config;


    /**
     * Redis constructor.
     * @param $config
     * @throws \BFF\Config\Exception
     */
    public function __construct($config)
    {
        $this->requiredConfigKeys = array(
            'host',
            'port',
            'blockTimeout'
        );

        $this->validateConfiguration($config);

        $this->config = $config;
    }

    /**
     * @throws \Exception
     */
    public function connect() {
        if (!$this->redis instanceof \Redis)
            $this->redis = new \Redis();

        if (!$this->redis->isConnected())
            if (!@$this->redis->pconnect($this->config['host'], $this->config['port']))
                throw new \Exception("Unable to connect to Redis: " . $this->redis->getLastError());
    }

    /**
     * @param $key string
     * @return string
     */
    public function rpop($key) {
        return $this->redis->rPop($key);
    }

    /**
     * @param $key string
     * @return array
     */
    public function brpop($key) {
        return $this->redis->brPop($key, $this->config['blockTimeout']);
    }

    /**
     * @param $key string
     * @return array
     */
    public function blpop($key) {
        return $this->redis->blPop($key, $this->config['blockTimeout']);
    }

    /**
     * @param $key string
     * @param $value string
     * @return int
     */
    public function lpush($key, $value) {
        return $this->redis->lPush($key, $value);
    }

    /**
     * @param $fromKey string
     * @param $toKey string
     * @return string
     */
    public function rpoplpush($fromKey, $toKey) {
        return $this->redis->rpoplpush($fromKey, $toKey);
    }

    /**
     * @param $fromKey string
     * @param $toKey string
     * @return string
     */
    public function brpoplpush($fromKey, $toKey) {
        return $this->redis->brpoplpush($fromKey, $toKey, $this->config['blockTimeout']);
    }

    /**
     * @param $key string
     * @param $value string
     * @return int
     */
    public function lrem($key, $value) {
        return $this->redis->lrem($key, $value, 1);
    }

    /**
     * @param $key string
     * @return int
     */
    public function llen($key) {
        return $this->redis->lLen($key);
    }

    /**
     * @param $key string
     * @param $start integer
     * @param $end integer
     * @return array
     */
    public function lrange($key, $start, $end) {
        return $this->redis->lRange($key, $start, $end);
    }

    /**
     * @param string $key
     * @return int
     */
    public function del(string $key) : int {
        return $this->redis->del($key);
    }
}