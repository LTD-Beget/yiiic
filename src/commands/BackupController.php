<?php

namespace yiiiconsole\commands;

use yii\console\Controller;
use yiiiconsole\commands\actions\backup\DeleteAction;
use yiiiconsole\commands\actions\backup\MakeAction;
use yiiiconsole\commands\actions\backup\RestoreAction;

class BackupController extends Controller
{

    public function options($actionID)
    {
        return [
            'date-range',
            'scope',
            'filter'
        ];
    }

    public function actions()
    {
        return [
            'make'    => MakeAction::class,
            'restore' => RestoreAction::class,
            'delete'  => DeleteAction::class
        ];
    }

}