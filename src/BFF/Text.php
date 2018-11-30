<?php

namespace BFF;

class Text
{
    public static function toCamel(string $inputString, bool $capitalFirst=false) : string
    {
        $intermediate = str_replace('_', '', ucwords($inputString, '_'));
        $final = str_replace('-', '', ucwords($intermediate, '-'));

        if (!$capitalFirst)
            $final = lcfirst($final);

        return $final;
    }
}