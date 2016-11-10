<?php


namespace LTDBeget\Yiiic\Events;


use LTDBeget\Yiiic\Context;
use yii\base\Event;

class ChangeContextEvent extends Event
{

    /**
     * @var Context
     */
    public $context;

}