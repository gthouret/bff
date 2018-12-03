<?php

namespace BFF\Test;

use BFF\Export;

class ExportTest extends TestCase
{
    /**
     * @var Export $export
     */
    private $export;

    public function setUp()
    {
        parent::setUp();
        $this->export = new Export();
        $testValues = array('test1', 1, true);

        $this->export->exporters['exportData'] = ['data_export', Export::TYPE_DATA, 'exportdata'];
        call_user_func(array($this->export,'exportData'), $testValues);
        call_user_func(array($this->export,'logApp'), $testValues);
    }

    public function testExportToRedis()
    {
        $queue = $this->export->getQueue();
        $item = $queue->rpop('testQueue1');
        self::assertTrue($item == json_decode($item));
        $item = $queue->rpop('testQueue2');
        self::assertTrue($item == json_decode($item));
    }

    public function testWriter() {
        list($queueName, $type, $filename) = $this->export->exporters['exportData'];
        $this->export->writeTimeout = 2;
        $this->export->writer($queueName, $type, $filename);

        $datafile = fopen('/tmp/exportdata', 'r');
        $line = fgets($datafile, 1024);
        $this->assertContains('"test1","1","1"', $line);
    }
}