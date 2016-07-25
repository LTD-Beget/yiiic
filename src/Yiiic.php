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
     * @var Dot
     */
    protected $params;

    /**
     * @var ArgsCompleterInterface
     */
    protected $argsCompleter;

    /**
     * @var ColFormatter
     */
    protected $colFormatter;

    /**
     * @var ReflectionInterface
     */
    protected $reflection;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $commandPrefix;

    /**
     * @var bool
     */
    protected $quit = false;

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
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param ColFormatterInterface $colFormatter
     */
    public function setColFormatter(ColFormatterInterface $colFormatter)
    {
        $this->colFormatter = $colFormatter;
    }

    /**
     * @param ArgsCompleterInterface $argsCompleter
     */
    public function setArgsCompleter(ArgsCompleterInterface $argsCompleter)
    {
        $this->argsCompleter = $argsCompleter;
    }

    public function run()
    {
        $this->registerCompleteFn();

        do {
            $this->printLayout();
            $line = $this->readInput();
            $this->handleInput($line);
        } while (!$this->quit);

        $this->printBye();
    }

    public function param(string $path, $default = NULL)
    {
        return $this->params->get($path, $default);
    }

    protected function readInput()
    {
        $line = trim(readline($this->loadPrompt()));
        readline_add_history($line);

        return $line;
    }

    /**
     * @param string $input
     * @param array $info
     *
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
                list($readContext, $null) = $this->parseServiceCommand(array_shift($args));
            }

            if ($readContext) {
                $args = ArrayHelper::merge($this->context->getAsArray(), $args);
            }

            try {
                $scope = $this->reflectByArgs($input, ...$args);
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
                $args = ArrayHelper::merge($this->context->getAsArray(), $args);
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
                $this->handleAppCommand($params);
                break;
            case $this->param('commands.context'):
                (new ContextHandler())->handle($this->context, $args);
                break;
            case $this->param('commands.quit'):
                $this->quit = true;
                break;
        }
    }

    protected function getScreenWidth() : int
    {
        return Console::getScreenSize(true)[0];
    }


    protected function loadPrompt()
    {
        exec(sprintf('echo "%s"', $this->getPrompt()));
    }

    /**
     * @return string
     */
    protected function getPrompt() : string
    {
        $prompt = 'yiiic';
        $route = $this->context->getAsString();

        if ($route) {
            $prompt .= ' ' . $this->context->getAsString();
        }

        return Console::ansiFormat($prompt . ': ', $this->param('style.prompt'));
    }

    protected function getHelpTitle(int $sizeContext)
    {
        $str = '';

        switch ($sizeContext) {
            case 0:
                $str = 'commands';
                break;
            case 1:
                $str = 'actions';
                break;
            case 2:
                $str = 'options';
                break;
        }

        return 'Available ' . $str;
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
     * @param array $scope
     *
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
     * @param array ...$args
     * @return array
     */
    protected function reflectByArgs($input = null, ...$args)
    {
        $count = count($args);

        if ($count === 0) {
            return $this->reflection->commands();
        }

        if ($count === 1) {
            return $this->reflection->actions($args[0]);
        }

        if (!$this->argsCompleter) {
            return $this->reflection->options($args[0], $args[1]);
        }

        $wantOptions = $input && strpos($input, ApiReflector::OPTION_PREFIX) === 0;

        if ($wantOptions) {
            return $this->reflection->options($args[0], $args[1]);
        }

        return $this->argsCompleter->getArgs($args[0], $args[1], $input, ...array_slice($args, 2));
    }

    /**
     * @param string $input
     *
     * @return array
     */
    protected function parseInput(string $input) : array
    {
        return preg_split('/\s+/', $input, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * @param string $command
     *
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
     * @param array $args
     *
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

    protected function printResultBorder()
    {
        $length = $this->getScreenWidth();
        $border = implode('', array_fill(0, $length - 1, $this->param('result_border')));
        $this->writer->writeln($border, $this->param('style.result.border'));
    }

    protected function printHelp()
    {
        $context = $this->context->getAsArray();
        $scope = $this->reflectByArgs(null, ...$context);
        $help = $this->colFormatter->format($scope, $this->param('height_help'), $this->getScreenWidth());
        $title = $this->getHelpTitle(count($context));

        $this->writer->writeln($title, $this->param('style.help.title'));
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

    protected function getDefaultParams() : array
    {
        return [
            'ignore' => ['yiiic', 'help'],
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