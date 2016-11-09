<?php


namespace LTDBeget\Yiiic\Events;

use yii\base\Event;

class AfterRunActionEvent extends Event
{

    /**
     * @var array
     */
    public $params;

    /**
     * @var int
     */
    public $exitCode = 0;

}