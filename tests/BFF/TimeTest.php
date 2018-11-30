<?php

namespace BFF\Test;

use BFF\Time;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{
    public function testTimestampToMysqlFormat()
    {
        // Temporarily force switch to UTC when testing
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');

        $this->assertEquals('2018-05-02 07:56:13', Time::timestampToMySqlFormat(1525247773));

        date_default_timezone_set($tz);
    }

    public function testTsAtStartOfTodayUTC()
    {
        $expected = time() - (time() % 86400);
        $this->assertEquals($expected, Time::tsAtStartOfTodayUTC());
    }

    public function testTsAtStartOfTodayUTCOffset()
    {
        $expected = time() - (time() % 86400) + (5 * 3600);

        if ($expected > time())
            $expected -= 86400;

        $this->assertEquals($expected, Time::tsAtStartOfTodayUTCOffset(-5));
    }
}