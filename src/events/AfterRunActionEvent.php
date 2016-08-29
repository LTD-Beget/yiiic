<?php


namespace LTDBeget\Yiiic\events;

use yii\base\Event;

class AfterRunActionEvent extends Event
{

    /**
     * @var array
     */
    public $params;

}