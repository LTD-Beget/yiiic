<?php

namespace LTDBeget\Yiiic;

interface ColFormatterInterface
{

    /**
     * @param array $data
     * @param int   $heightCol
     * @param int   $widthRow
     *
     * @return string
     */
    public function format(array $data, int $heightCol, int $widthRow) : string;

}