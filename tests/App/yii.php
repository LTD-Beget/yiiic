#!/usr/bin/env php
<?php

error_reporting(-1);

/**
 * Yii console bootstrap file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

defined('YII_DEBUG') or define('YII_DEBUG', true);

$root = __DIR__ . '/../..';

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('LTDBeget/Yiiic/Tests', $root . '/tests/');

$application = new yii\console\Application([
    'id' => 'test-app',
    'basePath' => $root . '/tests/App',
    'controllerNamespace' => 'LTDBeget\Yiiic\Tests\App\Controllers'
//    'components' => [
//        'yiiic' => [
//            'class' => \LTDBeget\Yiiic\Yiiic::class,
//        ]
//    ]
]);

$exitCode = $application->run();
exit($exitCode);
