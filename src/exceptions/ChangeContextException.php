<?php

namespace yiiiconsole\exceptions;

use Exception;

class ChangeContextException extends InvalidCommandException
{

    public function __construct($code = 0, \Exception $previous = NULL)
    {
        $message = 'Invalid format command, for change context use: <command> [<action>] -c';
        parent::__construct($message, $code, $previous);
    }

}