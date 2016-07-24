<?php

namespace LTDBeget\Yiiic;

interface WriterInterface
{

    /**
     * @param string $input
     * @param array $format
     *
     * @return void
     */
    public function write(string $input, array $format = []);

    /**
     * @param string $input
     * @param array $format
     *
     * @return void
     */
    public function writeln(string $input = '', array $format = []);

}