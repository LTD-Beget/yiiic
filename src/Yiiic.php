<?php

namespace LTDBeget\Yiiic;

use LTDBeget\Yiiic\Events\AfterRunActionEvent;
use LTDBeget\Yiiic\Events\BeforeRunActionEvent;
use LTDBeget\Yiiic\Exceptions\InvalidEntityException;
use Smarrt\Dot;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use LTDBeget\Yiiic\Exceptions\ApiReflectorNotFoundException;
use LTDBeget\Yiiic\Handlers\CommonHandler;
use LTDBeget\Yiiic\Handlers\HelpHandler;
use LTDBeget\Yiiic\Handlers\ContextHandler;

class Yiiic extends Component
{

    const EVENT_BEFORE_RUN_ACTION = 'beforeRunAction';
    const EVENT_AFTER_RUN_ACTION = 'afterRunAction';

    /**
     * @var array
     */
    protected $_entities;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var ApiReflectorInterface
     */
    protected $apiReflector;

    /**
     * @var ColFormatter
     */
    protected $colFormatter;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var InputResolver
     */
    protected $inputResolver;

    /**
     * @var ArgsCompleterInterface
     */
    protected $argsCompleter;

    /**
     * @var bool
     */
    protected $quit = false;

    /**
     * @var bool
     */
    protected $helpShown = false;


    public function init()
    {
        $this->prepareConf();

        $this->context = new Context();
        $this->inputResolver = new InputResolver(array_values($this->_options['commands']), $this->_options['without_context_prefix']);
        $this->colFormatter = new ColFormatter();
        $this->writer = new Writer();

        try {
            $this->apiReflector = $this->buildEntity('apiReflector', ApiReflectorInterface::class);
        } catch (InvalidEntityException $e) {
            $this->printError($e->getMessage());
        }

    }

    /**
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        $this->_options = $options;

    }

    /**
     * @param array $entities
     */
    public function setEntities(array $entities = [])
    {
        $this->_entities = $entities;
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
//        $this->registerCompleteFn();
//        $this->printWelcome();
//
//        do {
            $this->resolvePrintHelp();
//            $line = $this->readInput();
//            $this->handleInput($line);
//        } while (!$this->quit);
////
//        $this->printBye();
    }

    /**
     * @param string $input
     */
    protected function handleInput(string $input)
    {
        try {
            list($args, $command) = $this->inputResolver->parse($input, $this->context->getAsArray());

            if ($command) {
                return $this->handleServiceCommand($command, $args);
            }

            if (empty($args)) {
                return $this->printNotice('Try to type some command please :)');
            }

            $params = (new CommonHandler())->handle($args);

            return $this->handleAppCommand($params);
        } catch (\Throwable $e) {

            if ($this->_options['show_trace']) {
                $this->printError($e->getTraceAsString());
            }

            return $this->printError(sprintf('yiiic handle input: %s', $e->getMessage()));
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
            $args = $this->inputResolver->parse($buffer, $this->context->getAsArray())[0];

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

        $event = new BeforeRunActionEvent();
        $event->params = $params;
        $this->trigger(self::EVENT_BEFORE_RUN_ACTION, $event);

        $exitCode = 0;

        try {

            $this->runAction($params);

        } catch (ProcessFailedException $e) {
            $exitCode = $e->getProcess()->getExitCode();
            $this->printError("The Symfony Process Component report: ");
            $this->printError($e->getMessage());
        } finally {
            $event = new AfterRunActionEvent();
            $event->params = $params;
            $event->exitCode = $exitCode;
            $this->trigger(self::EVENT_AFTER_RUN_ACTION, $event);

            $this->writer->writeln();
            $this->printResultBorder();
            $this->writer->writeln();
        }

    }

    /**
     * @param array $params
     */
    protected function runAction(array $params)
    {
        $process = new Process($this->buildCliCmd($params));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->writer->writeln($process->getOutput(), $this->_options['style.result.content']);
    }

    /**
     * @param string $command
     * @param array $args
     */
    protected function handleServiceCommand(string $command, array $args)
    {
        switch ($command) {
            case $this->_options['commands.help']:
                $params = (new HelpHandler())->handle($args);
                $this->handleAppCommand($params);
                break;
            case $this->_options['commands.context']:
                (new ContextHandler())->handle($this->context, $args);
                break;
            case $this->_options['commands.quit']:
                $this->quit = true;
                break;
        }
    }

    protected function getScreenWidth() : int
    {
        return Console::getScreenSize(true)[0];
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
     * @param null $input
     * @param array ...$args
     * @return array
     */
    protected function reflectByArgs($input = null, ...$args)
    {
        $count = count($args);

        if ($count === 0) {
            return $this->apiReflector->controllers();
        }

        if ($count === 1) {
            return $this->apiReflector->actions($args[0]);
        }

        if (!$this->argsCompleter) {
            return $this->apiReflector->options($args[0], $args[1]);
        }

        $wantOptions = $input && strpos($input, ApiReflectorInterface::OPTION_PREFIX) === 0;

        if ($wantOptions) {
            return $this->apiReflector->options($args[0], $args[1]);
        }

        return $this->argsCompleter->getArgs($args[0], $args[1], $input, ...array_slice($args, 2));
    }

    protected function resolvePrintHelp()
    {
        switch ($this->_options['show_help']) {
            case Conf::SHOW_HELP_ALWAYS:
                $this->printHelp();
                break;
            case Conf::SHOW_HELP_ONCE:

                if (!$this->helpShown) {
                    $this->printHelp();
                    $this->helpShown = true;
                }

                break;
        }
    }

    protected function buildEntity(string $name, string $interface)
    {
        $entity = $this->_entities[$name]($this->_options);

        if (!($entity instanceof $interface)) {
            throw new InvalidEntityException(sprintf('%s must implement %s', $name, $interface));
        }

        return $entity;
    }

    protected function prepareConf()
    {
        $default = Conf::getDefault();
        $cli = Conf::getFromCLI();

        $this->_options = new Dot(ArrayHelper::merge($default['options'], $this->_options ?? [], $cli['options'] ?? []));

        if ($this->_options['entry_script'] === Conf::ENTRY_SCRIPT_CURRENT) {
            $this->_options['entry_script'] = realpath($_SERVER['argv'][0]);
        }

        $this->_entities = ArrayHelper::merge($default['entities'], $this->_entities ?? [], $cli['entities'] ?? []);
    }

    /**
     * @param array $args
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function buildCliCmd(array $args) : string
    {
        $args = array_map(function ($elem) {
            if (($count = count(explode(' ', $elem))) > 1) {
                $elem = '"' . $elem . '"';
            }

            return $elem;
        }, $args);

        $phpbin = 'php';
        $entryPoint = $this->_options['entry_script'];
        array_unshift($args, $phpbin, $entryPoint);

        $cmd = implode(" ", $args);

        if (!is_executable((new ExecutableFinder())->find($phpbin))) {
            throw new \RuntimeException(sprintf("command <%s>: %s point must be executable", $cmd, $phpbin));
        }

        if (!is_executable($entryPoint)) {
            throw new \RuntimeException(sprintf("command <%s>: entry script <%s> must be executable", $cmd, $entryPoint));
        }

        return $cmd;
    }

    protected function printResultBorder()
    {
        $length = $this->getScreenWidth();
        $border = implode('', array_fill(0, $length - 1, $this->_options['style.result.separator']));
        $this->writer->writeln($border, $this->_options['style.result.border']);
    }

    protected function printHelp()
    {
        $context = $this->context->getAsArray();
        $scope = $this->reflectByArgs(null, ...$context);
        $help = $this->colFormatter->format($scope, $this->_options['height_help'], $this->getScreenWidth());
        $title = $this->getHelpTitle(count($context));

        $this->writer->writeln($title, $this->_options['style.help.title']);
        $this->writer->writeln($help, $this->_options['style.help.content']);
    }

//    protected function printWelcome()
//    {
//        $this->writer->writeln();
//        $this->writer->writeln('Welcome to yii interactive console!', $this->_options['style.welcome']);
//        $this->writer->writeln();
//        $this->writer->writeln('docs https://github.com/LTD-Beget/yiiic');
//        $this->writer->writeln();
//    }
//
//    protected function printPrompt()
//    {
//        $this->writer->write($this->getPrompt(), $this->_options['style.prompt']);
//    }
//
//    protected function printBye()
//    {
//        $this->writer->writeln();
//        $this->writer->writeln('Bye!:)', $this->_options['style.bye']);
//        $this->writer->writeln();
//    }

    protected function printNotice(string $notice)
    {
        $this->writer->writeln();
        $this->writer->writeln($notice, $this->_options['style.notice']);
        $this->writer->writeln();
    }

    protected function printError(string $message)
    {
        $this->writer->writeln();
        $this->writer->writeln($message, $this->_options['style.error']);
        $this->writer->writeln();
    }
}