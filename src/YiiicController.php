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
     * @var bool Show trace after throw exception
     */
    public $trace;

    public function actionIndex()
    {
        $cliParams = [];

        if ($this->config) {

            if (!file_exists($this->config)) {
                throw new InvalidParamException(sprintf("Config file not found (%s)", $this->config));
            }

            $cliParams = include $this->config;
        }

        if ($this->trace != null) {
            $cliParams['show_trace'] = (bool)$this->trace;
        }

        Configuration::setParamsFromCLI($cliParams);

        (\Yii::$app->get('yiiic'))->run();
    }

    public function options($actionID)
    {
        return [
            'config',
            'trace'
        ];
    }

}