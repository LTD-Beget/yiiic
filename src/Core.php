<?php


namespace LTDBeget\Yiiic;


use Smarrt\Dot;
use yii\base\Component;


class Core extends Component
{

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

    public function setReflector(\Closure $closure)
    {
        $this->_reflector = $closure;
    }

    public function setOptions(array $options)
    {
        $this->_options = $options;
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

}