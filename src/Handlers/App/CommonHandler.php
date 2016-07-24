<?php

namespace LTDBeget\Yiiic\Handlers\App;

use LTDBeget\Yiiic\Exceptions\InvalidCommandException;
use LTDBeget\Yiiic\Route;

class CommonHandler
{

    /**
     * @param array $args
     *
     * @return array
     * @throws InvalidCommandException
     */
    public function handle(array $args) : array
    {
        if (count($args) < 2) {
            throw new InvalidCommandException('Valid command format: <controller> <action> [args] [options]');
        }

        $route = (new Route(... array_slice($args, 0, 2)))->getAsString();
        $args  = implode(' ', array_slice($args, 2));

        return [$route, $args];
    }

}