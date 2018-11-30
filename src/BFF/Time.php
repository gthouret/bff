<?php

namespace BFF;

class Time
{
    const HALF_HOUR = 1800;
    const ONE_HOUR = 3600;
    const TWO_HOUR = 7200;
    const ONE_DAY = 86400;
    const ONE_MIN = 60;
    const FIVE_MIN = 300;
    const TEN_MIN = 600;

    public static function timestampToMySqlFormat(int $timestamp) : string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    public static function tsAtStartOfTodayUTC() : int
    {
        return time() - (time() % Time::ONE_DAY);
    }

    public static function tsAtStartOfTodayUTCOffset(int $offsetHours) : int
    {
        $offsetSeconds = self::ONE_HOUR * $offsetHours;
        $startTime = self::tsAtStartOfTodayUTC();
        $offsetStart = $startTime - $offsetSeconds;

        if ($offsetStart > time())
            $offsetStart -= self::ONE_DAY;

        return $offsetStart;
    }
}