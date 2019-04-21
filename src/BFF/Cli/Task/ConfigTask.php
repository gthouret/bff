<?php

namespace BFF\Cli\Task;

use BFF\Cli\Task;
use BFF\Services;

class ConfigTask extends Task
{
    public function defaultAction()
    {
        print_r(Services::config()->toArray());
    }

    public function jsonAction()
    {
        echo json_encode(Services::config()->toArray(), JSON_PRETTY_PRINT);
    }
}