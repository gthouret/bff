<?php

namespace BFF\Cli\Task;

use BFF\Cli\Task;
use BFF\Service;

class ConfigTask extends Task
{
    public function defaultAction()
    {
        print_r(Service::config()->toArray());
    }

    public function jsonAction()
    {
        echo json_encode(Service::config()->toArray(), JSON_PRETTY_PRINT);
    }
}