<?php

namespace BFF;

use Exception;

class Logger
{
    const LEVEL_INFO = 1;
    const LEVEL_WARN = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_CRITICAL = 4;
    const LEVEL_DEBUG = 5;

    const LEVEL_LABEL = [
        self::LEVEL_INFO => 'info',
        self::LEVEL_WARN => 'warning',
        self::LEVEL_ERROR => 'error',
        self::LEVEL_CRITICAL => 'critical',
        self::LEVEL_DEBUG => 'debug'
    ];

    public static function logApp(string $prefix, int $level, string $message)
    {
        $data = [
            $prefix,
            '[' . strtoupper(self::LEVEL_LABEL[$level]) . ']',
            $message
        ];

        try {
            Services::export()->logApp($data);
        } catch (Exception $e) {
            trigger_error($e->getMessage());
        }
    }
}