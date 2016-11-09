<?php


namespace LTDBeget\Yiiic\Tests\App\Controllers\Actions\Test;


use yii\base\Action;
use yii\helpers\Console;

class FooAction extends Action
{

    /**
     * Example class action
     */
    public function run()
    {
        Console::stdout("Hello, i'm test/foo action");
    }

}