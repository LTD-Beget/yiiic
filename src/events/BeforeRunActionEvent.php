<?php


namespace LTDBeget\Yiiic\events;


use yii\base\Event;

class BeforeRunActionEvent extends Event
{

    /**
     * @var array
     */
    public $params;

}