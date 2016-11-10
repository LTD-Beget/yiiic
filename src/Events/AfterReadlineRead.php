<?php


namespace LTDBeget\Yiiic\Events;


use yii\base\Event;

class AfterReadlineRead extends Event
{

    /**
     * @var string
     */
    public $line;
}