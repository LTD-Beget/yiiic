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

$root = __DIR__ . '/..';

require $root . '/vendor/autoload.php';
require $root . '/vendor/yiisoft/yii2/Yii.php';

Yii::setAlias('LTDBeget/Yiiic', $root . '/src/');
Yii::setAlias('LTDBeget/Dev', $root . '/dev/');

$application = new yii\console\Application([
    'id' => 'yii-console',
    'basePath' => $root . '/src',
    'controllerNamespace' => 'LTDBeget\Dev\Controllers',
    'components' => [
        'yiiic' => [
            'class' => \LTDBeget\Yiiic\Yiiic::class,
        ]
    ]
]);