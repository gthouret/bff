<?php

namespace BFF;

use BFF\Registry\RegistryException;

class Registry
{
    /**
     * @var array
     */
    private static $services = [];

    /**
     * @param string $service
     * @return mixed
     * @throws RegistryException
     */
    public static function &get(string $service) {
            if (!isset(self::$services[$service]))
                throw new RegistryException('Service \'' . $service . '\' not found');

            return self::$services[$service];
    }

    /**
     * @param string $service
     * @return bool
     */
    public static function isset(string $service) : bool {
        return isset(self::$services[$service]);
    }

    /**
     * @param string $service
     * @param $object
     * @return bool
     */
    public static function set(string $service, $object) : bool {
        if (isset(self::$services[$service]))
            return false;
        else
            self::$services[$service] = $object;

        return true;
    }

    /**
     * @param string $service
     * @return bool
     */
    public static function remove(string $service) : bool {
        if (!isset(self::$services[$service]))
            return false;
        else
            unset(self::$services[$service]);

        return true;
    }

    /**
     *
     */
    public static function removeAll() {
        self::$services = [];
    }

    /**
     * @param string $service
     * @param $object
     * @return bool
     */
    public static function replace(string $service, $object) : bool
    {
        if (isset(self::$services[$service]))
            unset(self::$services[$service]);

        self::$services[$service] = $object;

        return true;
    }
}