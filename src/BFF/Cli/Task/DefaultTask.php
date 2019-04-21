<?php

namespace BFF\Cli\Task;

use BFF\Cli\Task;

class DefaultTask extends Task
{
    public function defaultAction()
    {
        echo $this->usage();
    }

    private function usage() : string
    {
        $usageString = "Usage: bff-cli [command] [options]" . PHP_EOL;
        $usageString .= "commands:" . PHP_EOL;
        $usageString .= "    config [json]\tOutputs the current configuration, optionally in json format" . PHP_EOL;
        $usageString .= "    version\t\tDisplay current tool version" . PHP_EOL;
        return $usageString;
    }
}