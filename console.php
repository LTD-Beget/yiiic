#!/usr/bin/env php
<?php

use yii\helpers\Console;

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

Yii::setAlias('yiiiconsole', __DIR__ . '/src/');

$application = new yii\console\Application([
    'id' => 'yii-console',
    'basePath' => __DIR__ . '/src',
    'controllerNamespace' => 'yiiiconsole\commands',
    'components' => [
        'yiiic' => [
            'class' => 'yiiiconsole\Yiiic',
            'ignore' => ['interactive'],
            'ui' => [
                'heightCol' => 5
            ],
            'style' => [
                'welcome' => [Console::FG_YELLOW, Console::BOLD],
                'help' => [
                    'title' => [Console::FG_GREEN, Console::UNDERLINE],
                    'scope' => [Console::FG_GREEN, Console::ITALIC]
                ],
                'error' => [Console::BG_RED]
            ]
        ]
    ]
]);

$exitCode = $application->run();
exit($exitCode);
