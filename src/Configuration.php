<?php

namespace LTDBeget\Yiiic;
use yii\helpers\Console;

/**
 * Global dirty configuration class
 */
class Configuration
{

    const SHOW_HELP_ALWAYS = 'always';
    const SHOW_HELP_NEVER = 'never';
    const SHOW_HELP_ONCE = 'once';

    /**
     * @var array
     */
    protected static $cliParams = [];

    public static function getParamsDefault()
    {
        return [
            'ignore' => ['yiiic', 'help'],
            'prompt' => 'yiiic',
            'show_help' => self::SHOW_HELP_ONCE,
            'show_trace' => false,
            'commands' => [
                'context' => 'c',
                'quit' => 'q',
                'help' => 'h'
            ],
            'without_context_prefix' => '/',
            'height_help' => 5,
            'result_border' => '=',
            'style' => [
                'prompt' => [Console::FG_GREEN, Console::BOLD],
                'welcome' => [Console::FG_YELLOW, Console::BOLD],
                'bye' => [Console::FG_YELLOW, Console::BOLD],
                'notice' => [Console::FG_YELLOW, Console::BOLD],
                'help' => [
                    'title' => [Console::FG_YELLOW, Console::UNDERLINE],
                    'scope' => [Console::FG_YELLOW, Console::ITALIC]
                ],
                'result' => [
                    'border' => [Console::FG_CYAN]
                ],
                'error' => [Console::FG_RED, Console::BOLD]
            ]
        ];
    }

    /**
     * @return array
     */
    public static function getParamsFromCLI() : array
    {
        return self::$cliParams;
    }

    /**
     * @param array $params
     */
    public static function setParamsFromCLI(array $params)
    {
        self::$cliParams = $params;
    }

}