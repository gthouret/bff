<?php

namespace BFF\Db\Adapter;

use BFF\Db\PdoDsn;
use BFF\Db\Exception as DbException;
use BFF\Config;
use BFF\Config\exception as ConfigException;
use PDO;
use PDOException;

class PdoFactory
{
    /**
     * @param $config Config
     * @param $host string
     * @param $disablePersistent bool
     * @return PDO
     * @throws DbException
     * @throws \Exception
     */

    public static function make(Config $config, string $host=null, bool $disablePersistent=false) : PDO
    {
        $options = [];
        $configKey = is_null($host) ? 'db' : 'db_' . $host;
        $dbConfig = $config->get($configKey);

        if (is_null($dbConfig))
            throw ConfigException::keyNotFound($configKey);

        if (isset($dbConfig['sslkey'])) {
            $options[PDO::MYSQL_ATTR_SSL_KEY] = $dbConfig['sslkey'];
        }

        if (isset($dbConfig['sslcert'])) {
            $options[PDO::MYSQL_ATTR_SSL_CERT] = $dbConfig['sslcert'];
        }

        if (isset($dbConfig['sslca'])) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = $dbConfig['sslca'];
        }

        if (!$disablePersistent && isset($dbConfig['pdo_persistent'])) {
            $options[PDO::ATTR_PERSISTENT] = $dbConfig['pdo_persistent'];
        }

        $options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_SILENT;
        $options[PDO::ATTR_EMULATE_PREPARES] = false;

        try {
            $dsn = PdoDsn::fromConfig($dbConfig);
            $dbAdapter = new ReliablePdo($dsn, $dbConfig['user'], $dbConfig['pass'], $options);
        } catch (PDOException $e) {
            throw new DbException('PdoFactory: ' . $e->getMessage());
        }

        return $dbAdapter;
    }
}