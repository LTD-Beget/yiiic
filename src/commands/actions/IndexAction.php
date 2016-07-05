<?php

namespace yiiiconsole\commands\actions;


use yii\base\Action;
use yiiiconsole\ColFormatter;
use yiiiconsole\Route;
use yiiiconsole\ApiReflector;
use yiiiconsole\Writer;
use yiiiconsole\Yiiic;

class IndexAction extends Action
{

    public function run()
    {
        /**
         * @var Yiiic $yiiic
         */
        $yiiic = \Yii::$app->get('yiiic');

        $yiiic->setApiReflector(new ApiReflector($yiiic->getIgnore()));
        $yiiic->setColFormatter(new ColFormatter());
        $yiiic->setWriter(new Writer());
        $yiiic->setRoute(new Route());

        while (true) {
            
            $yiiic->printLayout();
            
            $line = $yiiic->readInput();
            
            $yiiic->handleInput($line);
        }
    }

}