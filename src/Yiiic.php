<?php

namespace yiiiconsole;

use yii\base\Component;
use yii\console\Request;
use yii\helpers\ArrayHelper;
use yiiiconsole\exceptions\ChangeContextException;
use yiiiconsole\exceptions\InvalidCommandException;
use yiiiconsole\context\BaseContext;
use yiiiconsole\ApiReflector;

class Yiiic extends Component
{

    const CONTEXT_COMMAND = 'command';
    const CONTEXT_ACTION  = 'action';
    const CONTEXT_OPTIONS = 'options';
    const OPTION_CONTEXT  = '-c';

    protected $ignore = [];
    protected $ui;
    protected $style;

    protected $isRegisterComplete = false;

    protected $context = self::CONTEXT_COMMAND;

    /**
     * @var Writer
     */
    protected $writer;

    /**
     * @var Route
     */
    protected $route;

    /**
     * @var ApiReflector
     */
    protected $apiReflector;

    /**
     * @var ColFormatter
     */
    protected $colFormatter;

    /**
     * @return mixed
     */
    public function getUi()
    {
        return $this->ui;
    }

    /**
     * @param mixed $ui
     */
    public function setUi($ui)
    {
        $this->ui = $ui;
    }

    /**
     * @return mixed
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param mixed $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
    }

    /**
     * @return array
     */
    public function getIgnore() : array
    {
        return $this->ignore;
    }

    /**
     * @param array $ignore
     */
    public function setIgnore(array $ignore = [])
    {
        $this->ignore = $ignore;
    }

    /**
     * @param mixed $writer
     */
    public function setWriter(Writer $writer)
    {
        $this->writer = $writer;
    }

    /**
     * @param ApiReflector $apiReflector
     */
    public function setApiReflector(ApiReflector $apiReflector)
    {
        $this->apiReflector = $apiReflector;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @param ColFormatter $colFormatter
     */
    public function setColFormatter(ColFormatter $colFormatter)
    {
        $this->colFormatter = $colFormatter;
    }

    public function readInput()
    {
        if (!$this->isRegisterComplete) {
            $this->registerCompleteFn();
            $this->isRegisterComplete = true;
        }

        $line = trim(readline($this->getPrompt()));
        readline_add_history($line);

        return $line;
    }

    public function handleInput(string $input)
    {
        try {
            $segments = array_filter($this->parseCommand($input));

            if (empty($segments)) {
                return;
            }

            $pos = array_search(self::OPTION_CONTEXT, $segments);

            if ($pos !== false) {

                $segments = array_diff($segments, [self::OPTION_CONTEXT]);

                $context = $this->route->getAsArray();

                $segments = array_slice(array_filter(ArrayHelper::merge($context, $segments)), 0);

                $count = count($segments);

                if ($count >= 3) {
                    throw new ChangeContextException();
                }

                switch ($count) {
                    case 1:
                        $this->route->setControllerID($segments[0]);
                        $this->context = self::CONTEXT_ACTION;
                        break;
                    case 2:
                        $this->route->setControllerID($segments[0]);
                        $this->route->setActionID($segments[1]);
                        $this->context = self::CONTEXT_OPTIONS;
                        break;
                    default:
                        throw new \RuntimeException('Something is wrong...');
                }

                return;
            }

            $count = count($segments);

            if ($count < 2) {
                throw new InvalidCommandException('Command must contain <command> and <action>');
            }

            $route = (new Route(... array_slice($segments, 0, 2)))->getAsString();
            $args  = implode(' ', array_slice($segments, 2));

            $this->runCommand($route, $args);
        } catch (InvalidCommandException $e) {
            $this->printError($e->getMessage());
        }
    }

    public function printLayout()
    {
        $this->writer->writeln();
        $this->writer->writeln('Welcome to yii interactive console!', $this->getStyle()['welcome']);
        $this->writer->writeln();

        $this->printHelp();
    }

    protected function printHelp()
    {
        list($title, $help) = $this->getContextInfo();

        $this->writer->writeln(sprintf('Available %s:', $title), $this->getStyle()['help']['title']);

        $help = $this->colFormatter->format($help, $this->getUi()['heightCol']);
        $this->writer->writeln($help, $this->getStyle()['help']['scope']);
    }

    protected function getContextInfo()
    {
        switch ($this->context) {
            case self::CONTEXT_COMMAND:
                return ['commands', $this->apiReflector->commands()];
            case self::CONTEXT_ACTION:
                return ['actions', $this->apiReflector->actions($this->route->getControllerID())];
            case self::CONTEXT_OPTIONS:
                return ['options', $this->apiReflector->options(...$this->route->getAsArray())];
            default:
                return ['commands', $this->apiReflector->commands()];
        }
    }

    protected function getPrompt()
    {
        $prefix = 'yiiic: ';
        $route  = array_filter($this->route->getAsArray());

        return $prefix . implode('/', $route) . ' ';
    }

    protected function registerCompleteFn()
    {
        /**
         * @uses Yiiic::onComplete()
         */
        readline_completion_function([$this, 'onComplete']);
    }

    protected function onComplete($input)
    {
        return $this->completeHandler($input, readline_info());
    }

    protected function completeHandler($input, array $info)
    {
//        $line = substr($info['line_buffer'], 0, $info['end']);
        $buffer = $this->parseCommand($info['line_buffer']);

        if (!empty($info)) {
            array_pop($buffer);
        }

        $segments = ArrayHelper::merge($this->route->getAsArray(), $buffer);
        $segments = array_slice(array_filter($segments), 0);
        $count    = count($segments);

        switch ($count) {
            case 0: //case [<command...>]|[TAB]
                $commands = $this->apiReflector->commands();

                return $this->getComplete($input, $commands);
            case 1: // case <command> [<action...>]|[TAB]
                $actions = $this->apiReflector->actions($segments[0]);

                return $this->getComplete($input, $actions);
            default: // >=2 case <command> <action> [<option...>]|[TAB]
                $options = $this->apiReflector->options($segments[0], $segments[1]);

                return $this->getComplete($input, $options);
        }
    }

    protected function printError(string $message)
    {
        $this->writer->writeln();
        $this->writer->write($message, $this->getStyle()['error']);
        $this->writer->writeln();
    }

    protected function getComplete(string $input, array $scope) : array
    {
        if (empty($input)) {
            return $scope;
        }

        $complete = array_filter($scope, function ($elem) use ($input) {
            return stripos($elem, $input) === 0;
        });

        //if return empty, script can throw SEGFAULT
        if (empty($complete)) {
            return [''];
        }

        return $complete;
    }

    protected function runCommand(string $route, string $args)
    {
        $request = new Request(['params' => [$route, $args]]);

        \Yii::$app->handleRequest($request);
    }

    protected function parseCommand(string $command) : array
    {
        return preg_split('/\s+/', $command, -1, PREG_SPLIT_NO_EMPTY);
    }

}