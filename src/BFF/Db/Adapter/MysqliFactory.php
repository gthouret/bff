<?php

namespace BFF\Db\Adapter;

use BFF\Config;
use BFF\Db\Exception;
use BFF\Config\Exception as ConfigException;
use mysqli;

class MysqliFactory
{
    public static function make(Config $config, string $host=null, $persistent=true) : mysqli
    {
        $configKey = is_null($host) ? 'db' : 'db_' . $host;
        $dbConfig = $config->get($configKey);

        if (is_null($dbConfig))
            throw ConfigException::keyNotFound($configKey);

        $dbHost = ($persistent ? 'p:' : '') . $dbConfig['host'];

        if (!isset($dbConfig['charset']))
            throw ConfigException::keyNotFound('db::charset');

        $mysqli = mysqli_init();
        $mysqli->real_connect(
            $dbHost,
            $dbConfig['user'],
            $dbConfig['pass'],
            $dbConfig['dbname'],
            $dbConfig['port'] ?? null,
            $dbConfig['socket'] ?? null,
            $dbConfig['flags'] ?? null
        );

        if (mysqli_connect_errno()) {
            throw Exception::connectError(mysqli_connect_error());
        }

        $mysqli->set_charset($dbConfig['charset']);
        mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ERROR);

        return $mysqli;
    }
}