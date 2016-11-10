<?php


namespace LTDBeget\Yiiic;


use LTDBeget\Yiiic\Exceptions\InvalidParamException;
use yii\console\Controller;
use yii\di\ServiceLocator;

class YiiicController extends Controller
{

    /**
     * @var string Path to yiiic config file
     */
    public $config;

    /**
     * @var bool See options.show_trace
     */
    public $trace;

    /**
     * @var string See options.entry_script
     */
    public $script;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var Readline
     */
    protected $readline;

    /**
     * @var Printer
     */
    protected $printer;


    /**
     * @param string $id
     * @param \yii\base\Module $module
     * @param Core $core
     * @param array $config
     */
    public function __construct($id, $module, Core $core, array $config = [])
    {
        $cli = $this->getCLIConfiguration();

        $main = [];

        $reflector = $core->getReflector();
        $options = $core->getOptions();

        if ($reflector !== null) {
            $main['reflector'] = $reflector;
        }

        if ($options !== null) {
            $main['options'] = $options;
        }

        $conf = new Configuration($main);
        $conf->setCli($cli);
        $c = $conf->build();

        $core->configure($c);

//        $this->prepareYiiic($core);

        $this->printer = new Printer($c['options.style']);
        $readline = new Readline($c['options.prompt'], $c['options.style.prompt']);
        $context = new Context();

        $core->setContext($context);

        $readline->on(Readline::EVENT_BEFORE_READ, [$core, 'resolveHelp']);




        $this->readline = $readline;

        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {
        $this->printer->printWelcome();
        $this->readline->read();
        $this->printer->printBye();
    }

    public function options($actionID)
    {
        return [
            'config',
            'trace',
            'script'
        ];
    }

    protected function prepareYiiic(Yiiic $yiiic)
    {
        //custom setting
    }

    /**
     * @return array
     * @throws InvalidParamException
     */
    protected function getCLIConfiguration() : array
    {
        $cli = [];

        if ($this->config) {

            if (!file_exists($this->config)) {
                throw new InvalidParamException(sprintf("Config file not found (%s)", $this->config));
            }

            $cli = require $this->config;
        }

        if ($this->trace !== null) {
            $cli['options']['show_trace'] = (bool)$this->trace;
        }

        if ($this->script !== null) {
            $cli['options']['entry_script'] = $this->script;
        }

        return $cli;
    }

}