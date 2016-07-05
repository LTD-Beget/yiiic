<?php

namespace yiiiconsole\commands;

use yii\console\Controller;

use yiiiconsole\commands\actions\IndexAction;

/**
 * Interactive console mode
 */
class YiiicController extends Controller
{
    
    public function actions()
    {
        return [
            'index' => IndexAction::class
        ];
    }

    /**
     * @param string $actionID
     *
     * @return array
     */
    public function options($actionID)
    {
        return ['config'];
    }

}