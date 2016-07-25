<?php

namespace LTDBeget\Yiiic\Handlers;

use LTDBeget\Yiiic\Exceptions\ContextHandlerException;
use LTDBeget\Yiiic\Context;

class ContextHandler
{

    /**
     * @param Context $route
     * @param array $args
     *
     * @throws ContextHandlerException
     */
    public function handle(Context $route, array $args)
    {
        $count = count($args);

        if ($count > 2) {
            throw new ContextHandlerException('Invalid format context command');
        }

        $cid = '';
        $aid = '';

        switch ($count) {
            case 1:
                $cid = $args[0];
                break;
            case 2:
                $cid = $args[0];
                $aid = $args[1];
                $route->setControllerID($args[0]);
                $route->setActionID($args[1]);
                break;
        }

        $route->setControllerID($cid);
        $route->setActionID($aid);
    }

}