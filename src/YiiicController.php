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
     * @param string $id
     * @param \yii\base\Module $module
     * @param Core $core
     * @param array $config
     */
    public function __construct($id, $module, Core $core, array $config = [])
    {
        $cli = $this->getCLIConfiguration();

        $main = [
            'reflector' => $core->getReflector(),
            'options' => $core->getOptions()
        ];


        $conf = new Configuration($main);
        $conf->setCli($cli);
        $c = $conf->build();

        var_dump($c);
        $core->configure($c);

//        $this->prepareYiiic($core);

        $readline = new Readline($c['options.prompt'], $c['options.style.prompt']);
        $writer = new Writer();

        $core->on(Readline::EVENT_BEFORE_READ, function($event) use ($writer, $c) {
            $writer->writeln();
            $writer->writeln('Welcome to yii interactive console!', $c['options.style.welcome']);
            $writer->writeln();
            $writer->writeln('docs https://github.com/LTD-Beget/yiiic');
            $writer->writeln();
        });

        $this->readline = $readline;

        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {
        $this->readline->read();
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