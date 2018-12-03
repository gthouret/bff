<?php

namespace BFF\Db;

class PdoDsn
{
    public static function fromConfig(array $config) : string
    {
        return 'mysql' . ':' . 'dbname=' . $config['dbname'] . ';' . 'host=' . $config['host'];
    }
}