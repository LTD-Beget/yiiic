<?php

namespace Yiiic;

use Smarrt\Dot;
use yii\base\Component;
use yii\console\Request;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use Yiiic\Exceptions\ApiReflectorNotFoundException;
use Yiiic\Exceptions\InvalidCommandException;
use Yiiic\Handlers\App\CommonHandler;
use Yiiic\Handlers\App\HelpHandler;
use Yiiic\Handlers\Yiiic\BackHandler;
use Yiiic\Handlers\Yiiic\ContextHandler;

class Yiiic extends Component
{

    const COMMAND_CONTEXT = '-c';
    const COMMAND_BACK    = '-b';
    const COMMAND_QUIT    = '-q';
    const COMMAND_HELP    = '?';

    /**
     * @var bool
     */
    protected $receivedQuitCommand = false;

    /**
     * @var Dot
     */
    protected $params;

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
        $line = trim(readline($this->getPrompt()));
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

            if ($this->hasHelpCommand($args)) {
                return $this->handleHelpCommand($args);
            }

            $segments = ArrayHelper::merge($this->route->getAsArray(), $args);

            if (empty($segments)) {
                return $this->printNotice('Try to type some command please :)');
            }

            $serviceCommand = $this->extractYiiicCommand($segments);

            if ($serviceCommand !== false) {
                return $this->handleYiiicCommand($serviceCommand, $segments);
            }

            $params = (new CommonHandler())->handle($segments);

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

    protected function handleHelpCommand(array $args)
    {
        $params = array_slice(array_diff($args, [$this->param('commands.help')]), 0);
        $params = (new HelpHandler())->handle($params);

        return $this->handleAppCommand($params);
    }

    protected function handleYiiicCommand(string $command, array $args)
    {
        $args = array_diff($args, [$command]);

        switch ($command) {
            case $this->param('commands.context_enter'):
                (new ContextHandler())->handle($this->route, $args);
                break;
            case $this->param('commands.context_quit'):
                (new BackHandler())->handle($this->route);
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

    protected function getContextInfo()
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

    protected function getPrompt()
    {
        $prefix = 'yiiic: ';
        $route  = $this->route->getAsString();

        if ($route) {
            return $prefix . $this->route->getAsString() . ' ';
        }

        return $prefix;
    }

    /**
     * @param array $args
     *
     * @return string | false
     * @throws InvalidCommandException
     */
    protected function extractYiiicCommand(array $args)
    {
        $list  = array_slice(array_intersect($this->getServiceCommands(), $args), 0);
        $count = count($list);

        if (!$count) {
            return false;
        }

        if ($count > 1) {
            throw new InvalidCommandException(sprintf('You can pass one service command (received %s)', implode(', ', $list)));
        }

        return $list[0];
    }

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

    protected function completeHandler(string $input, array $info)
    {
        try {
            $buffer = $this->parseInput(substr($info['line_buffer'], 0, $info['end']));

            if (!empty($input)) {
                array_pop($buffer);
            }

            $segments = $buffer;

            if ($this->hasHelpCommand($segments)) {
                $segments = array_slice(array_diff($segments, [$this->param('commands.help')]), 0);
            } else {
                $segments = ArrayHelper::merge($this->route->getAsArray(), $buffer);
                // slice for reset key and true count elems
                $segments = array_slice($segments, 0);
            }

            try {
                $scope = $this->getCompleteScope($segments);
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

    protected function getCompleteScope(array $segments)
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

    protected function hasHelpCommand(array $args)
    {
        return array_search($this->param('commands.help'), $args) !== false;
    }

    protected function parseInput(string $input) : array
    {
        return preg_split('/\s+/', $input, -1, PREG_SPLIT_NO_EMPTY);
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
            'ignore'        => ['interactive', 'help'],
            'commands'      => [
                'context_enter' => '-c',
                'context_quit'  => '-b',
                'quit'          => '-q',
                'help'          => '?'
            ],
            'height_help'   => 5,
            'result_border' => '=',
            'style'         => [
                'welcome' => [Console::FG_YELLOW, Console::BOLD],
                'bye'     => [Console::FG_YELLOW, Console::BOLD],
                'notice'  => [Console::FG_YELLOW, Console::BOLD],
                'help'    => [
                    'title' => [Console::FG_YELLOW, Console::UNDERLINE],
                    'scope' => [Console::FG_YELLOW, Console::ITALIC]
                ],
                'result'  => [
                    'border' => [Console::FG_CYAN]
                ],
                'error'   => [Console::BG_RED]
            ]
        ];
    }

}