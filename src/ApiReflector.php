<?php

namespace yiiiconsole;

use yii\console\Controller;
use yii\console\controllers\HelpController;

/**
 * Class ApiReflector
 *
 * @package yiiiconsole
 */
class ApiReflector
{

    const OPTIONS_PREFIX = '--';

    /**
     * @var \ReflectionClass
     */
    protected $class;

    /**
     * @var HelpController
     */
    protected $obj;

    /**
     * @var array[]
     */
    protected $api;

    public function __construct(array $ignore = [])
    {
        $this->class = new \ReflectionClass(HelpController::class);
        $this->obj   = $this->class->newInstanceWithoutConstructor();
        $this->api   = $this->buildApi($ignore);
    }

    /**
     * @return array
     */
    public function commands() : array
    {
        return array_keys($this->api);
    }

    /**
     * @param string $command
     *
     * @return array
     */
    public function actions(string $command) : array
    {
        return array_keys($this->api[$command]);
    }

    /**
     * @param string $command
     * @param string $action
     * @param string $prefix
     *
     * @return array
     */
    public function options(string $command, string $action = 'index', $prefix = self::OPTIONS_PREFIX) : array
    {
        $options = $this->api[$command][$action];

        if ($prefix !== NULL) {
            $options = array_map(function ($elem) use ($prefix) {
                return $prefix . $elem;
            }, $options);
        }

        return $options;
    }

    protected function buildApi(array $ignore = []) : array
    {
        $api = [];

        $commands = $this->getCommands($ignore);

        foreach ($commands as $c) {
            /**
             * @var Controller $controller
             */
            list($controller, $null) = \Yii::$app->createController($c);
            $actions = $this->getActions($controller);

            $api[$c] = [];

            foreach ($actions as $a) {
                $api[$c][$a] = $controller->options($a);
            }
        }

        return $api;
    }

    protected function getCommands(array $ignore = []) : array
    {
        $method   = $this->class->getMethod('getCommands');
        $commands = $method->invoke($this->obj);

        return array_diff($commands, $ignore);
    }

    protected function getActions(Controller $controller)
    {
        $method = $this->class->getMethod('getActions');

        return $method->invoke($this->obj, $controller);
    }

}