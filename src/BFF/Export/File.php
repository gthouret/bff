<?php

namespace BFF\Export;

use BFF\Export;

class File implements Sink
{
    private $file;
    private $type;
    private $filepath;

    public function __construct($type, $filename, $exportPath)
    {
        $this->type = $type;

        if ($this->type == Export::TYPE_LOG)
            $filename .= '.log';

        $this->filepath = $exportPath . '/' . $filename;
    }

    public function setup()
    {
        $this->openForWriting();
    }

    private function openForWriting()
    {
        $this->file = fopen($this->filepath, 'a+');
        if ($this->file == false) {
            throw new \Exception('Unable to open file ' . $this->filepath . ' for writing');
        }
    }

    public function tearDown()
    {
        $this->close();
    }

    private function close()
    {
        fclose($this->file);
    }

    public function writeLine($line)
    {
        fwrite($this->file, $line);
    }

    public function handleRotate() {
        $this->close();
        $this->openForWriting();
    }
}