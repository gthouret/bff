<?php

namespace BFF\Cli\Task;

use BFF\Cli\Task;
use BFF\Services;

class VersionTask extends Task
{
    public function defaultAction()
    {
        $application = Services::config()->get('application');
        echo $application['version'];
    }
}