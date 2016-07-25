<?php


namespace LTDBeget\Yiiic;

interface ArgsCompleterInterface
{

    /**
     * @param $controllerID
     * @param $actionID
     * @param $input
     * @param array ...$args
     * @return array
     */
    public function getArgs($controllerID, $actionID, $input, ...$args) : array;

}