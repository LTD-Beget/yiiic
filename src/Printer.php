<?php

namespace LTDBeget\Yiiic;


use yii\helpers\Console;

class Printer
{

    /**
     * @var array
     */
    protected $style;

    /**
     * @var NULL|resource
     */
    protected $stream;

    /**
     * Printer constructor.
     * @param array $style
     * @param resource|NULL $stream
     */
    public function __construct(array $style, resource $stream = NULL)
    {
        $this->style = $style;

        if ($stream === NULL) {
            $stream = STDIN;
        }

        $this->stream = $stream;
    }

    public function printWelcome()
    {
        $this->writeln();
        $this->writeln('Welcome to yii interactive console!', $this->style['welcome']);
        $this->writeln();
        $this->writeln('docs https://github.com/LTD-Beget/yiiic');
        $this->writeln();
    }

    public function printBye()
    {
        $this->writeln();
        $this->writeln('Bye!:)', $this->style['bye']);
        $this->writeln();
    }

    public function printHelp()
    {

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