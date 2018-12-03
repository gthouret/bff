<?php

namespace BFF;

use BFF\Export\File;
use BFF\Export\Sink;
use BFF\Export\Stdout;
use BFF\Queue\Backend\Redis;

class Export
{
    const TYPE_DATA = 0;
    const TYPE_LOG = 1;

    const DATA_ENCLOSE = '"';
    const LINE_TERMINATE = "\n";
    const FIELD_SEPARATOR = ',';

    const LOG_UTC = true;

    /**
     * @var Redis
     */
    private $queue;

    /**
     * @var Sink
     */
    private $sink;

    /**
     * @var Config
     */
    private $config;

    private $writeToStdOutFlag = false;
    public $writeTimeout = 10;

    public $exporters = array();
    public $rotateLogSignal = false;

    public function __construct()
    {
        // Exporters: exporterName => [queueName, type, filename]
        $this->exporters['logApp'] = ['log_app', self::TYPE_LOG, 'application'];

        $this->config = Services::config();
        $this->queue = Services::queue();
    }

    public function getExporters() {
        return array_keys($this->exporters);
    }

    public function __call($name, $arguments)
    {
        if (array_key_exists($name, $this->exporters)) {
            $this->callExporter($name, $arguments);
        } else {
            if (stristr($name, 'Writer')) {
                $name = substr($name, 0, -6);
                if (array_key_exists($name, $this->exporters)) {
                    $this->callWriter($name);
                }
            } else {
                throw new \Exception('No Exporter or Writer found for ' . $name);
            }
        }
    }

    private function callExporter($name, $arguments) {
        list($queueName, $type, ) = $this->exporters[$name];

        $values = array();

        $tz = self::LOG_UTC ? new \DateTimeZone('UTC') : null;

        if ($type == self::TYPE_LOG)
            $values[] = (new \DateTime('now', $tz))->format(\DateTime::W3C);

        foreach ($arguments as $arg) {
            $values[] = $arg;
        }

        $this->pushToQueue($queueName, json_encode($values));
    }

    private function callWriter($name) {
        list($queueName, $type, $filename) = $this->exporters[$name];
        $this->writer($queueName, $type, $filename);
    }

    private function pushToQueue($queueName, $value)
    {
        if ($this->queue->lpush($queueName, $value) == false)
            throw new \Exception('Error pushing values to redis queue ' . $queueName);
    }

    public function writer($queueName, $type, $filename) {
        if ($this->writeToStdOutFlag)
            $this->sink = new Stdout();
        else
            $this->sink = new File($type, $filename, $this->config['paths']['export']);

        $this->sink->setup();

        declare(ticks=1);
        \pcntl_signal(SIGHUP, function () {
            $this->rotateLogSignal = true;
        });

        $timeoutTimer = $this->writeTimeout;
        $useTimeoutTimer = ($timeoutTimer > 0);

        while (true) {
            if ($this->rotateLogSignal) {
                $this->handleRotateLogSignal();
            }

            $item = $this->popFromQueue($queueName);
            if ($item == false) {
                if ($useTimeoutTimer)
                    $timeoutTimer--;
            } else {
                $line = $this->jsonToLine($item, $type);
                $this->sink->writeLine($line);
                if ($useTimeoutTimer)
                    $timeoutTimer = $this->writeTimeout;
            }

            if ($useTimeoutTimer && ($timeoutTimer == 0))
                break;
        }

        $this->sink->tearDown();
    }

    private function popFromQueue($queueName)
    {
        $itemArray = $this->queue->brpop($queueName);
        return (empty($itemArray)) ? false : $itemArray[1];
    }

    private function jsonToLine($json, $type) {
        $lineArray = json_decode($json);
        switch ($type) {
            case self::TYPE_LOG:
                $line = '[' . $lineArray[0] . ']';
                foreach ($lineArray[1] as $item) {
                    $line .= ' ';
                    if (is_string($item)) {
                        $line .= $item;
                    }
                }
                $line .= self::LINE_TERMINATE;
                break;

            case self::TYPE_DATA:
                $line = '';
                for ($i=0; $i < count($lineArray[0]); $i++) {
                    $line .= self::DATA_ENCLOSE . $this->escapeEnclosingCharacters($lineArray[0][$i]) . self::DATA_ENCLOSE;
                    if ($i < (count($lineArray[0]) - 1))
                        $line .= self::FIELD_SEPARATOR;
                }
                $line .= self::LINE_TERMINATE;
                break;

            default:
                throw new \Exception('Unknown type ' . $type . ', unable to convert to export line');
        }
        return $line;
    }

    public function getQueue() {
        return $this->queue;
    }

    private function handleRotateLogSignal()
    {
        $this->sink->handleRotate();
        $this->rotateLogSignal = false;
    }

    private function escapeEnclosingCharacters($string) {
        return str_replace(self::DATA_ENCLOSE, '\\'.self::DATA_ENCLOSE, $string);
    }

    public function writeToStdOut(bool $param=true) {
        $this->writeToStdOutFlag = $param;
    }

    public function setWriteTimeout(int $time)
    {
        $this->writeTimeout = $time;
    }
}