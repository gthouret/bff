<?php

namespace BFF\Patterns;

use BFF\Config\Exception;

trait ConfigurableTrait {
    private $requiredConfigKeys;

    private function validateConfiguration(array $config)
    {
        if (empty($config))
            throw new Exception("config should be an array of configuration options");

        if (empty($this->requiredConfigKeys))
            throw new Exception("requiredConfigKeys member should be an array of required configuration options");

        foreach ($this->requiredConfigKeys as $key) {
            $this->configurationKeyExists($key, $config);
        }
    }

    private function configurationKeyExists($configurationKey, $configurationArray) {
        if (!array_key_exists($configurationKey, $configurationArray))
            throw new Exception("Configuration key '$configurationKey' does not exist");
    }
}