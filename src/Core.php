<?php


namespace LTDBeget\Yiiic;


use Smarrt\Dot;
use yii\base\Component;
use yii\helpers\Console;


class Core extends Component
{

    const EVENT_SHOW_HELP = 'show.help';

    /**
     * @var mixed
     */
    protected $_reflector;

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var Dot
     */
    protected $conf;

    /**
     * @var Printer
     */
    protected $printer;

    /**
     * @var Context
     */
    protected $context;

    public function setReflector(\Closure $closure)
    {
        $this->_reflector = $closure;
    }

    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * @param Context $context
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return mixed
     */
    public function getReflector()
    {
        return $this->_reflector;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }


    public function init()
    {
//        $this->configure();
    }

    /**
     * @param Dot $conf
     */
    public function configure(Dot $conf)
    {
        $this->conf = $conf;
    }

    public function resolveHelp()
    {
        $help = '';
        switch ($this->conf['options.show_help']) {
            case Configuration::SHOW_HELP_ALWAYS:

                $this->printHelp();
                break;
            case Configuration::SHOW_HELP_ONCE:

                if (!$this->helpShown) {
                    $this->printHelp();
                    $this->helpShown = true;
                }

                break;
        }
    }

    protected function buildHelp()
    {
        $context = $this->context->getAsArray();
        $scope = $this->reflectByArgs(null, ...$context);
        $help = $this->colFormatter->format($scope, $this->_options['height_help'], $this->getScreenWidth());
        $title = $this->getHelpTitle(count($context));

        $this->writer->writeln($title, $this->_options['style.help.title']);
        $this->writer->writeln($help, $this->_options['style.help.content']);
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
     * @return int
     */
    protected function getScreenWidth() : int
    {
        return Console::getScreenSize(true)[0];
    }

}