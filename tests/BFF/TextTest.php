<?php

namespace BFF\Test;

use BFF\Text;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function testToCamel()
    {
        $tests = [
          'this-is-a-test',
          'this_is_a_test',
          'this_is-a_test'
        ];

        $outString = 'thisIsATest';
        foreach ($tests as $inString) {
            $this->assertEquals($outString, Text::toCamel($inString));
        }

        $outString = 'ThisIsATest';
        foreach ($tests as $inString) {
            $this->assertEquals($outString, Text::toCamel($inString, true));
        }
    }
}