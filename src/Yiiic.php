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
     * @var InputParser
     */
    protected $inputParser;

    /**
     * @var bool
     */
    protected $quit = false;

    /**
     * @var bool
     */
    protected $helpShown = false;

    /**
     * Yiiic constructor.
     * @param ReflectionInterface $reflection
     * @param WriterInterface $writer
     * @param InputParser $inputParser
     * @param Context $context
     * @param ColFormatter $colFormatter
     * @param array $config
     */
    public function __construct(
        ReflectionInterface $reflection,
        WriterInterface $writer,
        InputParser $inputParser,
        Context $context,
        ColFormatter $colFormatter,
        array $config
    )
    {
        $this->reflection = $reflection;
        $this->inputParser = $inputParser;
        $this->context = $context;
        $this->colFormatter = $colFormatter;
        $this->writer = $writer;

        parent::__construct($config);
    }

    /**
     * @param array $params
     * @return \Closure
     */
    public static function build(array $params = [])
    {
        return function () use ($params) {
            $params = ArrayHelper::merge(Yiiic::getDefaultParams(), $params);

            return new Yiiic(
                new ApiReflector($params['ignore']),
                new Writer(),
                new InputParser(array_values($params['commands']), $params['without_context_prefix']),
                new Context(),
                new ColFormatter(),
                ['params' => $params]
            );
        };
    }

    public static function getDefaultParams() : array
    {
        return [
            'ignore' => ['yiiic', 'help'],
            'prompt' => 'yiiic',
            'show_help' => ParamsInterface::SHOW_HELP_ONCE,
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
                'error' => [Console::BG_RED]
            ]
        ];
    }

    /**
     * @param array $params
     */
    public function setParams(array $params = [])
    {
        $params = ArrayHelper::merge($this->getDefaultParams(), $params);
        $this->params = Dot::with($params);
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
        $this->printWelcome();

        do {
            $this->resolvePrintHelp();
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
     */
    protected function handleInput(string $input)
    {
        try {
            list($args, $command) = $this->inputParser->parse($input, $this->context->getAsArray());

            if ($command) {
                return $this->handleServiceCommand($command, $args);
            }

            if (empty($args)) {
                return $this->printNotice('Try to type some command please :)');
            }

            $params = (new CommonHandler())->handle($args);

            return $this->handleAppCommand($params);
        } catch (\Throwable $e) {
            $this->printError(sprintf('yiiic handle input: %s', $e->getMessage()));
            $this->printError($e->getTraceAsString());
        }
    }

    /**
     * readline_completion_function callback
     *
     * @param string $input
     * @return array
     */
    protected function onComplete(string $input)
    {
        try {
            $buffer = $this->prepareBuffer($this->getLineBuffer(), $input);
            $args = $this->inputParser->parse($buffer, $this->context->getAsArray())[0];

            try {
                $scope = $this->reflectByArgs($input, ...$args);
            } catch (ApiReflectorNotFoundException $e) {
                return $this->preventSegfaultValue();
            }

            return $this->getComplete($input, $scope);
        } catch (\Throwable $e) {
            //TODO: make terminate interactive mode?
            $this->printError(sprintf('readline complete fail: %s', $e->getTraceAsString()));

            return $this->preventSegfaultValue();
        }
    }

    /**
     * @param array $params
     */
    protected function handleAppCommand(array $params)
    {
        $this->printResultBorder();
        $this->writer->writeln();

        $this->runAction($params);

        $this->writer->writeln();
        $this->printResultBorder();
        $this->writer->writeln();
    }

    /**
     * @param array $params
     */
    protected function runAction(array $params)
    {
        \Yii::$app->handleRequest(new Request(['params' => $params]));
    }

    /**
     * @param string $command
     * @param array $args
     */
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
        return exec(sprintf('echo "%s"', $this->getPrompt()));
    }

    /**
     * @return string
     */
    protected function getPrompt() : string
    {
        $prompt = $this->param('prompt');
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
     * @uses Yiiic::onComplete()
     */
    protected function registerCompleteFn()
    {
        readline_completion_function([$this, 'onComplete']);
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
     * @return string
     */
    protected function getLineBuffer() : string
    {
        $info = $info = readline_info();

        return substr($info['line_buffer'], 0, $info['end']);
    }

    /**
     * @param string $buffer
     * @param string $input
     * @return string
     */
    protected function prepareBuffer(string $buffer, string $input) : string
    {
        if (!empty($input)) {
            return substr($buffer, 0, -(strlen($input) + 1));
        }

        return $buffer;
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

    protected function resolvePrintHelp()
    {
        switch ($this->param('show_help')) {
            case ParamsInterface::SHOW_HELP_ALWAYS:
                $this->printHelp();
                break;
            case ParamsInterface::SHOW_HELP_ONCE:

                if (!$this->helpShown) {
                    $this->printHelp();
                    $this->helpShown = true;
                }

                break;
        }
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

    protected function printWelcome()
    {
        $this->writer->writeln();
        $this->writer->writeln('Welcome to yii interactive console!', $this->param('style.welcome'));
        $this->writer->writeln();
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
        $this->writer->writeln();
    }
}