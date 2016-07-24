<?php

namespace LTDBeget\Yiiic\Handlers\Yiiic;

use LTDBeget\Yiiic\Route;

class BackHandler
{

    /**
     * @param Route $route
     */
    public function handle(Route $route)
    {
        if ($route->getActionID()) {
            $route->setActionID('');
        } elseif ($route->getControllerID()) {
            $route->setControllerID('');
        }
    }

}