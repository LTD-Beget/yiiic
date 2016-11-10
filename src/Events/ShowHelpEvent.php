<?php
/**
 * Created by PhpStorm.
 * User: ridzhi
 * Date: 11.11.16
 * Time: 2:11
 */

namespace LTDBeget\Yiiic\Events;


use yii\base\Event;

class ShowHelpEvent extends Event
{

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

}