<?php

namespace LTDBeget\Yiiic;

use yii\console\Controller;
use yii\console\controllers\HelpController;
use LTDBeget\Yiiic\Exceptions\ApiReflectorNotFoundException;
use LTDBeget\Yiiic\Exceptions\InvalidCommandException;

/**
 * Class ApiReflector
 *
 * @package yiiiconsole
 */
class ApiReflector implements ApiReflectorInterface
{

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
        $this->obj = $this->class->newInstanceWithoutConstructor();
        $this->api = $this->buildApi($ignore);
    }

    /**
     * @return array
     */
    public function controllers() : array
    {
        return array_keys($this->api);
    }

    /**
     * @param string $controllerID
     *
     * @return array
     * @throws InvalidCommandException
     */
    public function actions(string $controllerID) : array
    {
        $this->validateControllerID($controllerID);

        return array_keys($this->api[$controllerID]);
    }

    /**
     * @param string $controllerID
     * @param string $actionID
     * @param string $prefix
     *
     * @return array
     */
    public function options(string $controllerID, string $actionID = 'index', $prefix = ApiReflectorInterface::OPTION_PREFIX) : array
    {
        $this->validateActionID($controllerID, $actionID);

        $options = $this->api[$controllerID][$actionID];

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
            $controller = \Yii::$app->createController($c)[0];
            $actions = $this->getActions($controller);

            $api[$c] = [];

            foreach ($actions as $a) {
                $o = $controller->options($a);
                asort($o);
                $api[$c][$a] = $o;
            }
        }

        return $api;
    }

    protected function getCommands(array $ignore = []) : array
    {
        $method = $this->class->getMethod('getCommands');
        $commands = $method->invoke($this->obj);

        return array_diff($commands, $ignore);
    }

    protected function getActions(Controller $controller)
    {
        $method = $this->class->getMethod('getActions');

        return $method->invoke($this->obj, $controller);
    }

    /**
     * @param string $controllerID
     *
     * @throws ApiReflectorNotFoundException
     */
    protected function validateControllerID(string $controllerID)
    {
        if (!array_key_exists($controllerID, $this->api)) {
            throw new ApiReflectorNotFoundException(sprintf('Controller <%s> not found', $controllerID));
        }
    }

    /**
     * @param string $controllerID
     * @param string $actionID
     *
     * @throws ApiReflectorNotFoundException
     */
    protected function validateActionID(string $controllerID, string $actionID)
    {
        $this->validateControllerID($controllerID);

        if (!array_key_exists($actionID, $this->api[$controllerID])) {
            throw new ApiReflectorNotFoundException(sprintf('Action <%s/%s> not found', $controllerID, $actionID));
        }
    }

}