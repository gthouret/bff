<?php

return [
    'env' => 'production',
    'nodeId' => 1,
    'paths' => [
        'export' => '/tmp'
    ],
    'db' => [
        'host' => '127.0.0.1',
        'user' => 'root',
        'pass' => 'pw',
        'dbname' => 'bff-test',
        'charset' => 'utf8',
        'pdo_persistent' => false
    ],
    'memcache' => [
        'host' => '127.0.0.1',
        'port' => '11211',
        'pool' => 'bff-test'
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'blockTimeout' => 1
    ],
];