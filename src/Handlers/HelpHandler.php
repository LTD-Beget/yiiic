<?php

namespace LTDBeget\Yiiic\Handlers;

use LTDBeget\Yiiic\Exceptions\InvalidCommandException;

class HelpHandler
{

    public function handle(array $args)
    {
        $count = count($args);

        if ($count > 2) {
            throw new InvalidCommandException('Valid help format: help [controller] [action]');
        }

        $params = ['help'];

        if ($count) {
            $params[] = implode('/', $args);
        }

        return $params;
    }

}