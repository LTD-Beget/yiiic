<?php

namespace LTDBeget\Yiiic;

use LTDBeget\Yiiic\Exceptions\InvalidCommandException;
use yii\helpers\ArrayHelper;

class InputParser
{

    /**
     * @var array
     */
    protected $commands;

    /**
     * @var string
     */
    protected $nonContextPrefix;

    /**
     * InputParser constructor.
     * @param array $commands available service commands
     * @param string $nonContextPrefix marker for non context interpretation
     */
    public function __construct(array $commands, string $nonContextPrefix)
    {
        $this->commands = $commands;
        $this->nonContextPrefix = $nonContextPrefix;
    }

    /**
     * @param string $input
     * @param array $context current context args
     * @return array [$args, $command]
     */
    public function parse(string $input, array $context) : array
    {
        $args = $this->explode($input);
        $command = $this->extractCommand($args);

        $readContext = true;

        if ($command !== null) {
            $hasPrefix = strpos($command, $this->nonContextPrefix) === 0;
            $readContext = !$hasPrefix;

            if ($hasPrefix) {
                $command = substr($command, 1);
            }
        }

        if ($readContext) {
            $args = ArrayHelper::merge($context, $args);
        }

        return [$args, $command];
    }

    /**
     * @param array $args
     * @return string|null
     * @throws InvalidCommandException
     */
    protected function extractCommand(array &$args)
    {
        $matches = array_slice(preg_grep($this->getRegex(), $args), 0);
        $count = count($matches);

        if ($count === 0) {
            return null;
        }

        if ($count !== 1) {
            throw new InvalidCommandException(sprintf('Invalid input, you pass %s service commands', $count));
        }

        $args = array_slice(array_diff($args, $matches), 0);

        return $matches[0];
    }

    /**
     * @param string $input
     * @return array
     */
    protected function explode(string $input) : array
    {
        $tmp =  preg_split('/(".*?")/', $input, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $result = [];

        foreach ($tmp as $str) {
            $str = trim($str);

            if($str === '') {
                continue;
            }

            $new = (strpos($str, '"') === 0) ? [trim($str, '"')] : preg_split('/\s+/', $str, -1, PREG_SPLIT_NO_EMPTY);
            $result = array_merge($result, $new);
        }

        return $result;
    }

    /**
     * @return string
     */
    protected function getRegex() : string
    {
        return sprintf('/^(\%s?(%s))$/', $this->nonContextPrefix, implode('|', $this->commands));
    }

}