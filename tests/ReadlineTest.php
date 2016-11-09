<?php

namespace LTDBeget\Yiiic\Tests;

use yii\helpers\Console;

class ReadlineTest extends \PHPUnit_Framework_TestCase
{

    public function testBuild()
    {
        if (READLINE_LIB === "libedit") {
            Console::stdout("Warning: you using libedit as readline library. Some builds realize not correct behavior of readline_completion_function function. Reccomends using readline lib");
        }
    }

}
