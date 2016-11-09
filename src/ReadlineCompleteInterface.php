<?php

namespace LTDBeget\Yiiic;


interface ReadlineCompleteInterface
{

    /**
     * @param string $prev
     * @param string $current
     * @return array
     */
    public function complete(string $prev, string $current) : array;

}