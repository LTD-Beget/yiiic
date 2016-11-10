<?php


namespace LTDBeget\Yiiic;


use Smarrt\Dot;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

class Configuration
{

    const SHOW_HELP_ALWAYS = 'always';
    const SHOW_HELP_NEVER = 'never';
    const SHOW_HELP_ONCE = 'once';
    const ENTRY_SCRIPT_CURRENT = 'realpath($_SERVER[argv][0])';


    /**
     * @var array
     */
    protected $main;

    /**
     * @var array
     */
    protected $cli;

    /**
     * Configuration constructor.
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->main = $configuration;
    }

    /**
     * @param array $cli
     */
    public function setCli(array $cli)
    {
        $this->cli = $cli;
    }


    /**
     * @return Dot
     */
    public function build() : Dot
    {
        $conf = new Dot($this->merge());

        if ($conf['options.entry_script'] === self::ENTRY_SCRIPT_CURRENT) {
            $conf['options.entry_script'] = realpath($_SERVER['argv'][0]);
        }

        return $conf;
    }

    /**
     * @return array
     */
    protected function merge() : array
    {
        return ArrayHelper::merge($this->getDefault(), $this->main ?? [], $this->cli ?? []);
    }

    protected function getDefault()
    {
        return [
            'reflector' => function ($options) {
                return new ApiReflector($options['ignore']);
            },

            'options' => [
                'ignore' => ['yiiic', 'help'],
                'prompt' => 'yiiic',
                'show_help' => self::SHOW_HELP_ONCE,
                'show_trace' => false,
                'entry_script' => self::ENTRY_SCRIPT_CURRENT,
                'commands' => [
                    'context' => 'c',
                    'quit' => 'q',
                    'help' => 'h'
                ],
                'without_context_prefix' => '/',
                'height_help' => 5,
                'style' => [
                    'prompt' => [Console::FG_GREEN, Console::BOLD],
                    'welcome' => [Console::FG_YELLOW, Console::BOLD],
                    'bye' => [Console::FG_YELLOW, Console::BOLD],
                    'notice' => [Console::FG_YELLOW, Console::BOLD],
                    'help' => [
                        'title' => [Console::FG_YELLOW, Console::UNDERLINE],
                        'content' => [Console::FG_YELLOW, Console::ITALIC]
                    ],
                    'result' => [
                        'border' => [Console::FG_CYAN],
                        'content' => [Console::FG_CYAN],
                        'separator' => '='
                    ],
                    'error' => [Console::FG_RED, Console::BOLD]
                ]
            ],
        ];
    }

}