<?php

namespace yiiiconsole;

use yii\helpers\Console;

class Writer
{

    protected $stream;

    public function __construct(resource $stream = NULL)
    {
        if ($stream === NULL) {
            $stream = STDIN;
        }

        $this->stream = $stream;
    }

    public function write(string $input, array $format = [])
    {
        $this->w($input, $format);
    }

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