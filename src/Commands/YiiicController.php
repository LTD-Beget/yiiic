<?php

namespace LTDBeget\Yiiic\Commands;

use yii\console\Controller;

use LTDBeget\Yiiic\ApiReflector;
use LTDBeget\Yiiic\ColFormatter;
use LTDBeget\Yiiic\Context;
use LTDBeget\Yiiic\Writer;
use LTDBeget\Yiiic\Yiiic;

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
        $yiiic->setContext(new Context());

        $yiiic->run();
    }

}