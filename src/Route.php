<?php

namespace LTDBeget\Yiiic;

class Route
{

    /**
     * @var string
     */
    protected $controllerID = '';

    /**
     * @var string
     */
    protected $actionID = '';

    /**
     * Route constructor.
     *
     * @param string|NULL $controllerID
     * @param string|NULL $actionID
     */
    public function __construct(string $controllerID = '', string $actionID = '')
    {
        $this->controllerID = $controllerID;
        $this->actionID = $actionID;
    }

    /**
     * @param string $controllerID
     */
    public function setControllerID(string $controllerID)
    {
        $this->controllerID = $controllerID;
    }

    /**
     * @param string $actionID
     */
    public function setActionID(string $actionID)
    {
        $this->actionID = $actionID;
    }

    /**
     * @return string
     */
    public function getControllerID() : string
    {
        return $this->controllerID;
    }

    /**
     * @return string
     */
    public function getActionID() : string
    {
        return $this->actionID;
    }

    /**
     * @param string $separator
     * @return string
     */
    public function getAsString(string $separator = '/') : string
    {
        return implode($separator, $this->getAsArray());
    }

    /**
     * @return array
     */
    public function getAsArray() : array
    {
        return array_filter([$this->getControllerID(), $this->getActionID()]);
    }

}