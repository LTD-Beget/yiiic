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

$root = __DIR__ . '/..';

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('LTDBeget/Yiiic', $root . '/src/');

$application = new yii\console\Application([
    'id' => 'yii-console',
    'basePath' => $root . '/src',
    'controllerNamespace' => 'LTDBeget\Yiiic\Commands',
    'components' => [
        'yiiic' => [
            'class' => 'LTDBeget\Yiiic\Yiiic',
            'params' => []
        ]
    ]
]);

$exitCode = $application->run();
exit($exitCode);
