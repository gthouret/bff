<?php

namespace BFF\Config;

class Exception extends \Exception
{
    public static function keyNotFound(string $key) : self
    {
        throw new self('Config key' . $key . ' not found');
    }
}