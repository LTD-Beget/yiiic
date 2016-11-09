<?php


namespace LTDBeget\Yiiic\Events;


use yii\base\Event;

class BeforeRunActionEvent extends Event
{

    /**
     * @var array
     */
    public $params;

}