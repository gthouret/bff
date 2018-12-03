<?php

namespace BFF\Db;


class Exception extends \Exception
{
    public static function connectError(string $message) : self
    {
        return new self('Connection error: ' . $message);
    }
}