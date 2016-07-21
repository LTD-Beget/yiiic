<?php

namespace Yiiic\Commands;

use yii\console\Controller;

use Yiiic\ApiReflector;
use Yiiic\ColFormatter;
use Yiiic\Route;
use Yiiic\Writer;
use Yiiic\Yiiic;

/**
 * Interactive console mode
 */
class YiiicController extends Controller
{

    public function actionIndex()
    {
        /**
         * @var Yiiic $yiiic
         */
        $yiiic = \Yii::$app->get('yiiic');

        $yiiic->setReflection(new ApiReflector($yiiic->param('ignore')));
        $yiiic->setColFormatter(new ColFormatter());
        $yiiic->setWriter(new Writer());
        $yiiic->setRoute(new Route());

        $yiiic->run();
    }

}