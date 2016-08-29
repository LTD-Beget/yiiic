<?php


namespace LTDBeget\Yiiic;


use LTDBeget\Yiiic\Exceptions\InvalidParamException;
use yii\console\Controller;

class YiiicController extends Controller
{

    /**
     * @var string Path to yiiic config file
     */
    public $config;

    /**
     * @var bool See component config [options.show_trace]
     */
    public $trace;

    public function actionIndex()
    {
        $this->prepareConf();
        $yiiic = (\Yii::$app->get('yiiic'));
        $this->prepareYiiic($yiiic);
        $yiiic->run();
    }

    public function options($actionID)
    {
        return [
            'config',
            'trace'
        ];
    }

    protected function prepareYiiic(Yiiic $yiiic)
    {
        //custom setting
    }

    protected function prepareConf()
    {
        $cliConf = [];

        if ($this->config) {

            if (!file_exists($this->config)) {
                throw new InvalidParamException(sprintf("Config file not found (%s)", $this->config));
            }

            $cliConf = include $this->config;
        }

        if ($this->trace != null) {
            $cliConf['show_trace'] = (bool)$this->trace;
        }

        Conf::setFromCLI($cliConf);
    }

}