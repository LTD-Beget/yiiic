<?php

namespace yiiiconsole;

class Route
{
    
    /**
     * @var string
     */
    protected $controllerID;

    /**
     * @var string
     */
    protected $actionID;

    /**
     * Route constructor.
     *
     * @param string|NULL $controllerID
     * @param string|NULL $actionID
     */
    public function __construct(string $controllerID = '', string $actionID = '')
    {
        $this->controllerID = $controllerID;
        $this->actionID     = $actionID;
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
     * @return mixed
     */
    public function getControllerID() : string
    {
        return $this->controllerID;
    }

    /**
     * @return mixed
     */
    public function getActionID() : string
    {
        return $this->actionID;
    }

    public function getAsString(string $separator = '/') : string
    {
        return $this->getControllerID() . $separator . $this->getActionID();
    }

    public function getAsArray() : array
    {
        return [$this->getControllerID(), $this->getActionID()];
    }
}