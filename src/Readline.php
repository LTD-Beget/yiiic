<?php

namespace LTDBeget\Yiiic;

use yii\helpers\Console;

class Readline
{

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $prompt;

    /**
     * @var ReadlineCompleteInterface
     */
    protected $completer;

    /**
     * @var array
     */
    protected $style;

    /**
     * Readline constructor.
     * @param Context $context
     * @param string $prompt
     * @param array $style
     */
    public function __construct(Context $context, string $prompt, array $style)
    {
        $this->context = $context;
        $this->prompt = $prompt;
        $this->style = $style;
    }

    public function read()
    {
        do {
            $line = trim(readline($this->loadPrompt()));
            readline_add_history($line);

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
        $prompt = $this->prompt;
        $route = $this->context->getAsString();

        if ($route) {
            $prompt .= ' ' . $route;
        }

        return Console::ansiFormat($prompt . ': ', $this->style);
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