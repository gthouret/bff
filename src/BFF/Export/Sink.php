<?php

namespace BFF\Export;


interface Sink
{
    public function setup();
    public function writeLine($line);
    public function handleRotate();
    public function teardown();
}