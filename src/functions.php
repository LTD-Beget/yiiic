<?php

namespace LTDBeget\Yiiic;

use yii\helpers\ArrayHelper;

/**
 * If realize own implement, don't forgot merge default params
 * @param array $params
 * @return \Closure
 */
function build(array $params = [])
{
    return function () use ($params) {
        $params = ArrayHelper::merge(Configuration::getParamsDefault(), $params, Configuration::getParamsFromCLI());

        return new Yiiic(
            new ApiReflector($params['ignore']),
            new Writer(),
            new InputParser(array_values($params['commands']), $params['without_context_prefix']),
            new Context(),
            new ColFormatter(),
            ['params' => $params]
        );
    };
}
