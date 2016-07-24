<?php

namespace LTDBeget\Yiiic;

use yii\helpers\Console;

class Writer implements WriterInterface
{

    protected $stream;

    /**
     * Writer constructor.
     *
     * @param resource|NULL $stream
     */
    public function __construct(resource $stream = NULL)
    {
        if ($stream === NULL) {
            $stream = STDIN;
        }

        $this->stream = $stream;
    }

    /**
     * @param string $input
     * @param array $format
     */
    public function write(string $input, array $format = [])
    {
        $this->w($input, $format);
    }

    /**
     * @param string $input
     * @param array $format
     */
    public function writeln(string $input = '', array $format = [])
    {
        $this->w($input . PHP_EOL, $format);
    }

    protected function w(string $input, array $format = [])
    {
        fwrite($this->stream, $this->format($input, $format));
    }

    protected function format(string $input, array $format = [])
    {
        return Console::ansiFormat($input, $format);
    }

}