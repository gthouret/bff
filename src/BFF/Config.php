<?php

namespace BFF;

use BFF\Config\Exception;

class Config implements \ArrayAccess {

    const BASE_CONFIG_ENV = 'production';

    /**
     * @var array
     */
    private $config;
    private $env;
    private $configDir;

    public function __construct(string $env = '', string $configDir = __DIR__ . '/../../config')
    {
        $this->env = $env;
        $this->configDir = $configDir;

        $baseConfigFile = $this->configDir . '/' . static::BASE_CONFIG_ENV . '.php';
        $envConfigFile = $this->configDir . '/' . $env . '.php';

        $loadConfig = function($file) : array {
            if (file_exists($file)) {
                return include $file;
            } else {
                throw new \BFF\Exception('Config file not found: ' . $file);
            }
        };

        if (empty($env) || ($env === self::BASE_CONFIG_ENV)) {
            $this->config = $loadConfig($baseConfigFile);
        } else {
            $envConfig = $loadConfig($envConfigFile);
            $this->config = array_replace_recursive($this->config, $envConfig);
        }
    }

    public function get(string $name) {
        if (isset($this->config[$name]))
            return $this->config[$name];
        else
            throw new Exception('Config key ' . $name . ' not found');
    }

    /*
     * ArrayAccess Interface
     */

    public function offsetExists($offset)
    {
        return isset($this->config[$offset]);
    }

    public function &offsetGet($offset)
    {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }

    public function toArray() : array
    {
        return $this->config;
    }

    public function env() : string
    {
        return $this->env;
    }
}
