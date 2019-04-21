<?php

namespace BFF\Cli;

class Task
{
    const DEFAULT_TASK = 'Default';
    const DEFAULT_ACTION = 'default';

    public static function init(array $argv): void
    {
        $arguments = self::parseArguments($argv);
        $params = $arguments['params'] ?? [];

        if (empty($arguments['task'])) {
            $arguments['task'] = self::DEFAULT_TASK;
        }

        $className = self::class . '\\' . $arguments['task'] . 'Task';
        if (!class_exists($className)) {
            throw new Exception('Class ' . $className . ' does not exist');
        } else {
            $controller = new $className();
            $action = empty($arguments['action']) ? self::DEFAULT_ACTION : $arguments['action'];
            $methodName = $action . 'Action';

            if (method_exists($controller, '__call') || method_exists($controller, $methodName)) {
                $controller->$methodName($params);
            } else {
                throw new Exception('Action ' . $action . ' does not exist for task ' . $arguments['task']);
            }
        }
    }

    public static function parseArguments(array $argv): array
    {
        $arguments = [];

        foreach ($argv as $k => $arg) {
            if ($k == 1) {
                $arguments['task'] = ucfirst($arg);
            } elseif ($k == 2) {
                $arguments['action'] = $arg;
            } elseif ($k >= 3) {
                $arguments['params'][] = $arg;
            }
        }

        return $arguments;
    }
}