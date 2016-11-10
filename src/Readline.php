<?php

namespace LTDBeget\Yiiic;

use LTDBeget\Yiiic\Events\AfterReadlineRead;
use LTDBeget\Yiiic\Events\BeforeReadlineRead;
use LTDBeget\Yiiic\Events\ChangeContextEvent;
use yii\base\Component;
use yii\helpers\Console;

class Readline extends Component
{

    const EVENT_BEFORE_READ = 'before.read';
    const EVENT_AFTER_READ = 'after.read';

    /**
     * @var ReadlineCompleteInterface
     */
    protected $completer;

    /**
     * @var string
     */
    protected $prompt;

    /**
     * @var string
     */
    protected $defaultPrompt;

    /**
     * @var array
     */
    protected $style;


    /**
     * Readline constructor.
     * @param string $prompt
     * @param array $style
     * @param array $config
     */
    public function __construct(string $prompt, array $style, array $config = [])
    {
        $this->prompt = $this->defaultPrompt = $prompt;
        $this->style = $style;

        parent::__construct($config);
    }

    public function read()
    {
        do {
            $this->trigger(self::EVENT_BEFORE_READ);

            $line = trim(readline($this->loadPrompt()));
            readline_add_history($line);

            $event = new AfterReadlineRead();
            $event->line = $line;
            $this->trigger(self::EVENT_AFTER_READ, $event);

        } while ($line !== false && $line !== 'quit');
    }

    /**
     * @param ReadlineCompleteInterface $completer
     * @uses Readline::onCompletion()
     */
    public function registerCompleter(ReadlineCompleteInterface $completer)
    {
        $this->completer = $completer;
        readline_completion_function([$this, 'onCompletion']);
    }

    public function onChangeContext(ChangeContextEvent $event)
    {
        $this->prompt = $this->defaultPrompt;
        $context = $event->context->getAsString();

        if ($context) {
            $this->prompt .= ' ' . $context;
        }
    }

    /**
     * @param string $current
     * @return array
     */
    protected function onCompletion(string $current) : array
    {
        $prev = $this->getPrev($current);

        return $this->completer->complete($prev, $current);
    }

    /**
     * @return string
     */
    protected function loadPrompt() : string
    {
        return exec(sprintf('echo "%s"', $this->getPrompt()));
    }

    /**
     * @return string
     */
    protected function getPrompt() : string
    {
        return Console::ansiFormat($this->prompt . ': ', $this->style);
    }


    /**
     * @param string $input
     * @return string
     */
    protected function getPrev(string $input) : string
    {
        $line = $this->getLine();

        if ($input !== '') {
            $line = substr($line, 0, -(strlen($input) + 1));
        }

        return trim($line);
    }

    /**
     * @return string
     */
    protected function getLine() : string
    {
        $info = $this->getReadlineInfo();

        return substr($info['line_buffer'], 0, $info['end']);
    }

    /**
     * @return array
     */
    protected function getReadlineInfo() : array
    {
        return readline_info();
    }

}