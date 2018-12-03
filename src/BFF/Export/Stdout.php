<?php

namespace BFF\Export;


class Stdout implements Sink
{
    public function setup()
    {
        // TODO: Implement setup() method.
    }

    public function writeLine($line)
    {
        echo $line;
    }

    public function handleRotate()
    {
        // TODO: Implement handleRotate() method.
    }

    public function teardown()
    {
        // TODO: Implement teardown() method.
    }
}