<?php

namespace LTDBeget\Yiiic\Handlers;

use LTDBeget\Yiiic\Exceptions\InvalidCommandException;
use LTDBeget\Yiiic\Context;

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

        $route = (new Context(... array_slice($args, 0, 2)))->getAsString();
        $args = array_slice($args, 2);
        array_unshift($args, $route);

        return $args;
    }

}