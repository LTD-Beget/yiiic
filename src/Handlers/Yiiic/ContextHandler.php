<?php

namespace LTDBeget\Yiiic\Handlers\Yiiic;

use LTDBeget\Yiiic\Exceptions\ContextHandlerException;
use LTDBeget\Yiiic\Route;

class ContextHandler
{

    /**
     * @param Route $route
     * @param array $args
     *
     * @throws ContextHandlerException
     */
    public function handle(Route $route, array $args)
    {
        $count = count($args);

        if ($count >= 3) {
            throw new ContextHandlerException('Invalid format context command');
        }

        switch ($count) {
            case 1:
                $route->setControllerID($args[0]);
                break;
            case 2:
                $route->setControllerID($args[0]);
                $route->setActionID($args[1]);
                break;
            default:
                throw new ContextHandlerException('Context command require arg[s]');
        }
    }

}