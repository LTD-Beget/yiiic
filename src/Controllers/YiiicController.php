<?php

namespace LTDBeget\Yiiic\Controllers;

use yii\console\Controller;

/**
 * Interactive console mode
 */
class YiiicController extends Controller
{

    public function actionIndex()
    {
        (\Yii::$app->get('yiiic'))->run();
    }

}