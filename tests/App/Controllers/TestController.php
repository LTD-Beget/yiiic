<?php


namespace LTDBeget\Yiiic\Tests\App\Controllers;


use LTDBeget\Yiiic\Tests\App\Controllers\Actions\Test\FooAction;
use yii\console\Controller;
use yii\helpers\Console;

class TestController extends Controller
{

    public function actions()
    {
        return [
            'foo' => FooAction::class
        ];
    }

    /**
     * Example method action
     */
    public function actionBar()
    {
        Console::stdout("Hello, i'm test/bar action");
    }

}