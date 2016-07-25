<?php

namespace LTDBeget\Yiiic;

use Smarrt\Dot;
use yii\base\Component;
use yii\console\Request;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use LTDBeget\Yiiic\Exceptions\ApiReflectorNotFoundException;
use LTDBeget\Yiiic\Handlers\CommonHandler;
use LTDBeget\Yiiic\Handlers\HelpHandler;
use LTDBeget\Yiiic\Handlers\ContextHandler;

class Yiiic extends Component
{

    /**
     * @var bool
     */
    protected $receivedQuitCommand = false;

    /**
     * @var Dot
     */
    protected $params;

    /**
     * @var string
     */
    protected $commandPrefix;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var ReflectionInterface
     */
    protected $reflection;

    /**
     * @var ColFormatter
     */
    protected $colFormatter;

    /**
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $params = ArrayHelper::merge($this->getDefaultParams(), $params);
        $this->params = Dot::with($params);
        $this->commandPrefix = $this->param('command_prefix');
    }

    /**
     * @param WriterInterface $writer
     */
    public function setWriter(WriterInterface $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param ReflectionInterface $reflection
     */
    public function setReflection(ReflectionInterface $reflection)
    {
        $this->reflection = $reflection;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @param ColFormatterInterface $colFormatter
     */
    public function setColFormatter(ColFormatterInterface $colFormatter)
    {
        $this->colFormatter = $colFormatter;
    }

    public function run()
    {
        $this->registerCompleteFn();

        do {
            $this->printLayout();
            $this->printPrompt();
            $line = $this->readInput();
            $this->handleInput($line);
        } while (!$this->receivedQuitCommand);

        $this->printBye();
    }

    public function param(string $path, $default = NULL)
    {
        return $this->params->get($path, $default);
    }

    protected function readInput()
    {
        $line = trim(readline());
        readline_add_history($line);

        return $line;
    }

    /**
     * @param string $input
     */
    protected function handleInput(string $input)
    {
        try {
            $args = $this->parseInput($input);

            $readContext = true;
            $scmd = null;

            if ($this->hasServiceCommand($args)) {
                list($readContext, $scmd) = $this->parseServiceCommand(array_shift($args));
            }

            if ($readContext) {
                $args = ArrayHelper::merge($this->route->getAsArray(), $args);
            }

            if ($scmd) {
                return $this->handleServiceCommand($scmd, $args);
            }

            if (empty($args)) {
                return $this->printNotice('Try to type some command please :)');
            }

            $params = (new CommonHandler())->handle($args);

            return $this->handleAppCommand($params);
        } catch (\Throwable $e) {
            return $this->printError($e->getMessage());
        }
    }

    protected function handleAppCommand(array $params)
    {
        $this->printResultBorder();
        $this->writer->writeln();

        \Yii::$app->handleRequest(new Request(['params' => $params]));

        $this->writer->writeln();
        $this->printResultBorder();
        $this->writer->writeln();
    }

    protected function handleServiceCommand(string $command, array $args)
    {
        switch ($command) {
            case $this->param('commands.help'):
                $params = (new HelpHandler())->handle($args);
                return $this->handleAppCommand($params);
            case $this->param('commands.context'):
                (new ContextHandler())->handle($this->route, $args);
                break;
            case $this->param('commands.quit'):
                $this->receivedQuitCommand = true;
                break;
        }
    }

    protected function getScreenWidth() : int
    {
        return Console::getScreenSize(true)[0];
    }

    protected function printResultBorder()
    {
        $length = $this->getScreenWidth();
        $border = implode('', array_fill(0, $length - 1, $this->param('result_border')));
        $this->writer->writeln($border, $this->param('style.result.border'));
    }

    protected function printHelp()
    {
        list($title, $help) = $this->getContextInfo();
        $this->writer->writeln(sprintf('Available %s:', $title), $this->param('style.help.title'));
        $help = $this->colFormatter->format($help, $this->param('height_help'), $this->getScreenWidth());
        $this->writer->writeln($help, $this->param('style.help.scope'));
    }

    protected function printLayout()
    {
        $this->writer->writeln();
        $this->writer->writeln('Welcome to yii interactive console!', $this->param('style.welcome'));
        $this->writer->writeln();

        $this->printHelp();
    }

    protected function printPrompt()
    {
        $this->writer->write($this->getPrompt(), $this->param('style.prompt'));
    }

    protected function printBye()
    {
        $this->writer->writeln();
        $this->writer->writeln('Bye!:)', $this->param('style.bye'));
        $this->writer->writeln();
    }

    protected function printNotice(string $notice)
    {
        $this->writer->writeln();
        $this->writer->writeln($notice, $this->param('style.notice'));
        $this->writer->writeln();
    }

    protected function printError(string $message)
    {
        $this->writer->writeln();
        $this->writer->write($message, $this->param('style.error'));
        $this->writer->writeln();
    }

    /**
     * @return array
     */
    protected function getContextInfo() : array
    {
        $segments = $this->route->getAsArray();

        switch (count($segments)) {
            case 0:
                return ['commands', $this->reflection->commands()];
            case 1:
                return ['actions', $this->reflection->actions($segments[0])];
            case 2:
                return ['options', $this->reflection->options($segments[0], $segments[1])];
            default:
                return ['commands', $this->reflection->commands()];
        }
    }

    /**
     * @return string
     */
    protected function getPrompt() : string
    {
        $prompt = 'yiiic';
        $route = $this->route->getAsString();

        if ($route) {
            $prompt .= ' ' . $this->route->getAsString();
        }

        return $prompt . ': ';
    }

    /**
     * @return array
     */
    protected function getServiceCommands() : array
    {
        return array_values($this->param('commands'));
    }

    /**
     * @uses Yiiic::onComplete()
     */
    protected function registerCompleteFn()
    {
        readline_completion_function([$this, 'onComplete']);
    }

    protected function onComplete(string $input)
    {
        return $this->completeHandler($input, readline_info());
    }

    /**
     * @param string $input
     * @param array $info
     * @return array
     */
    protected function completeHandler(string $input, array $info)
    {
        try {
            $args = $this->parseInput($info['line_buffer']);

            if (!empty($input)) {
                array_pop($args);
            }

            $readContext = true;

            if ($this->hasServiceCommand($args)) {

                if ($this->isNonContextCommand(array_shift($args))) {
                    $readContext = false;
                }
            }

            if ($readContext) {
                $args = ArrayHelper::merge($this->route->getAsArray(), $args);
            }

            try {
                $scope = $this->getCompleteScope($args);
            } catch (ApiReflectorNotFoundException $e) {
                return $this->preventSegfaultValue();
            }

            return $this->getComplete($input, $scope);
        } catch (\Throwable $e) {
            //TODO: make terminate interactive mode?
            $this->printError(sprintf('readline complete fail: %s', $e->getMessage()));

            return $this->preventSegfaultValue();
        }
    }

    /**
     * @param string $input
     * @param array $scope
     * @return array
     */
    protected function getComplete(string $input, array $scope) : array
    {
        if (empty($input)) {

            if (empty($scope)) {
                return $this->preventSegfaultValue();
            }

            return $scope;
        }

        $complete = array_filter($scope, function ($elem) use ($input) {
            return stripos($elem, $input) === 0;
        });

        if (empty($complete)) {
            return $this->preventSegfaultValue();
        }

        return $complete;
    }

    /**
     * @param array $segments
     * @return array
     */
    protected function getCompleteScope(array $segments) : array
    {
        $count = count($segments);

        switch ($count) {
            case 0: //case [<command...>]|[TAB]
                return $this->reflection->commands();
            case 1: // case <command> [<action...>]|[TAB]
                return $this->reflection->actions($segments[0]);
            default: // >=2 case <command> <action> [<option...>]|[TAB]
                return $this->reflection->options($segments[0], $segments[1]);
        }
    }

    /**
     * @param string $input
     * @return array
     */
    protected function parseInput(string $input) : array
    {
        return preg_split('/\s+/', $input, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param string $command
     * @return array [isContext, Command]
     */
    protected function parseServiceCommand(string $command) : array
    {
        $nonContext = $this->commandPrefix . $this->commandPrefix;
        if (strpos($command, $nonContext) === 0) {
            return [false, substr($command, 2)];
        }

        return [true, substr($command, 1)];
    }

    /**
     * @param string $command
     * @return bool
     */
    protected function isNonContextCommand(string $command) : bool
    {
        return strpos($command, $this->getNonContextPrefix()) === 0;
    }

    /**
     * @return string
     */
    protected function getNonContextPrefix() : string
    {
        return $this->commandPrefix . $this->commandPrefix;
    }

    /**
     * @param array $args
     * @return bool
     */
    protected function hasServiceCommand(array $args) : bool
    {
        return !empty($args) && (strpos($args[0], $this->param('command_prefix')) === 0);
    }

    /**
     * If readline_complete_function return empty, script can throw SEGFAULT
     *
     * @return array
     */
    protected function preventSegfaultValue() : array
    {
        return [''];
    }

    protected function getDefaultParams() : array
    {
        return [
            'ignore' => ['interactive', 'help'],
            'commands' => [
                'context' => 'c',
                'quit' => 'q',
                'help' => 'h'
            ],
            'command_prefix' => ':',
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
                'error' => [Console::BG_RED]
            ]
        ];
    }

}