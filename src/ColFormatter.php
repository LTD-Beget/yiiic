<?php

namespace yiiiconsole;

use yii\helpers\Console;

class ColFormatter
{

    protected $padding = 2;

    public function format(array $data, int $heightCol)
    {
        $widthCol = $this->getWidthCol($data);
        $count = $this->getPossibleColNumber($data, $heightCol, $widthCol);
        $chunked = $this->chunkData($data, $count);

        return $this->getOutput($chunked, $widthCol);
    }

    /**
     * @param array $data
     * @param int   $heightCol
     * @param int   $widthCol
     *
     * @return int
     */
    protected function getPossibleColNumber(array $data, int $heightCol, int $widthCol) : int
    {
        // try to perform with client settings
        $widthScreen = $this->getWidthScreen();
        $number          = (int) ceil(count($data) / $heightCol);
        $availableWidth = (int) (ceil(($widthScreen - $this->padding) / $number) - $this->padding);

        $isCanBePlaced = $availableWidth >= $widthCol;

        // otherwise using auto calculation
        if(!$isCanBePlaced) {
            $number   = (int) ceil(($widthScreen - $this->padding) / $widthCol);
        }

        return $number;
    }
    
    /**
     * @param array $data
     * @param int   $chunks
     *
     * @return array[]
     */
    protected function chunkData(array $data, int $chunks) : array
    {
        return array_chunk($data, ceil(count($data) / $chunks));
    }

    /**
     * @param array $data
     *
     * @return int
     */
    protected function getWidthCol(array $data) : int
    {
        $max = 0;

        foreach ($data as $row) {
            $l = strlen($row);

            if ($l > $max) {
                $max = $l;
            }
        }

        return $max + $this->padding;
    }

    /**
     * @param array $data
     * @param int   $offset
     *
     * @return string
     */
    protected function getOutput(array $data, int $offset) : string
    {
        ob_start();

        Console::moveCursorDown();
        Console::saveCursorPosition();

        $pos = $this->padding;

        foreach ($data as $column) {

            foreach ($column as $cmd) {
                Console::moveCursorTo($pos);
                echo $cmd . PHP_EOL;
            }

            $pos += $offset;

            Console::restoreCursorPosition();
        }

        $rowsCount = count($data[0]);

        Console::moveCursorDown($rowsCount + 1);

        return ob_get_clean();
    }

    /**
     * @return int
     */
    protected function getWidthScreen() : int
    {
        return Console::getScreenSize(true)[0];
    }

}