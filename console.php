#!/usr/bin/env php
<?php


ini_set('display_errors', 1);
ini_set('error_reporting', -1);



/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);



require 'vendor/autoload.php';

require 'vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('Yiiic', __DIR__ . '/src/');

$application = new yii\console\Application([
    'id' => 'yii-console',
    'basePath' => __DIR__ . '/src',
    'controllerNamespace' => 'Yiiic\Commands',
    'components' => [
        'yiiic' => [
            'class' => 'Yiiic\Yiiic',
            'params' => []
        ]
    ]
]);

$exitCode = $application->run();
exit($exitCode);
