<?php

namespace BFF\Cli\Task;

use BFF\Cli\Task;
use BFF\Service;

class VersionTask extends Task
{
    public function defaultAction()
    {
        $application = Service::config()->get('application');
        echo $application['version'];
    }
}