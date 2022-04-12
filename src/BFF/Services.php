<?php

namespace BFF;

use BFF\Cache\Memcache;
use BFF\Db\Adapter\MysqliFactory;
use BFF\Db\Adapter\PdoFactory;
use BFF\Queue\Backend\Redis;
use PDO;

class Service
{
    const CACHE = 'cache';
    const EXPORT = 'export';
    const CONFIG = 'config';
    const QUEUE = 'queue';
    const PDO = 'pdo';
    const MYSQLI = 'mysqli';

    /**
     * @return Config
     * @throws Registry\RegistryException
     */
    public static function config() : Config
    {
        if (!Registry::isset(Service::CONFIG)) {
            $obj = new Config(getenv('APPLICATION_ENV'));
            Registry::set(Service::CONFIG, $obj);
            return $obj;
        } else {
            return Registry::get(static::CONFIG);
        }
    }

    /**
     * @return Memcache
     * @throws Config\Exception
     * @throws Registry\RegistryException
     */
    public static function cache() : Memcache
    {
        if (!Registry::isset(Service::CACHE)) {
            $obj = new Memcache(static::config()->get('memcache'));
            Registry::set(Service::CACHE, $obj);
            return $obj;
        } else {
            return Registry::get(static::CACHE);
        }
    }

    /**
     * @return Export
     * @throws Registry\RegistryException
     */
    public static function export() : Export
    {
        if (!Registry::isset(Service::EXPORT)) {
            $obj = new Export();
            Registry::set(Service::EXPORT, $obj);
            return $obj;
        } else {
            return Registry::get(static::EXPORT);
        }
    }

    /**
     * @return Redis
     * @throws Config\Exception
     * @throws Registry\RegistryException
     */
    public static function queue() : Redis
    {
        if (!Registry::isset(static::QUEUE)) {
            $obj = new Queue\Backend\Redis(static::config()->get('redis'));
            $obj->connect();
            Registry::set(static::QUEUE, $obj);
            return $obj;
        } else {
            return Registry::get(static::QUEUE);
        }
    }

    /**
     * @return PDO
     * @throws Db\Exception
     * @throws Registry\RegistryException
     */
    public static function pdo() : PDO
    {
        if (!Registry::isset(static::PDO)) {
            $obj = PdoFactory::make(static::config());
            Registry::set(static::PDO, $obj);
            return $obj;
        } else {
            return Registry::get(static::PDO);
        }
    }

    /**
     * @return \mysqli
     * @throws Config\Exception
     * @throws Db\Exception
     * @throws Registry\RegistryException
     */
    public static function mysqli() : \mysqli
    {
        if (!Registry::isset(static::MYSQLI)) {
            $obj = MysqliFactory::make(static::config());
            Registry::set(static::MYSQLI, $obj);
            return $obj;
        } else {
            return Registry::get(static::MYSQLI);
        }
    }
}
