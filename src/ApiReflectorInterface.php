<?php

namespace LTDBeget\Yiiic;

interface ApiReflectorInterface
{

    const OPTION_PREFIX = '--';

    /**
     * @return array
     */
    public function controllers() : array;

    /**
     * @param string $controllerID
     *
     * @return array
     */
    public function actions(string $controllerID) : array;

    /**
     * @param string $controllerID
     * @param string $actionID
     * @param string $prefix
     *
     * @return array
     */
    public function options(string $controllerID, string $actionID, $prefix = self::OPTION_PREFIX) : array;

}