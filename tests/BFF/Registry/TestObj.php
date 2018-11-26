<?php

namespace BFF\Test\Registry;

class TestObj
{
    private $test = 0;

    public function setTest(int $test)
    {
        $this->test = $test;
    }

    public function getTest() : int
    {
        return $this->test;
    }

    public function helloWorld() : string
    {
        return 'Hello World!';
    }
}