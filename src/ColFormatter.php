<?php

namespace LTDBeget\Yiiic;

class ColFormatter
{

    protected $padding = 2;

    /**
     * @param array $data
     * @param int $heightCol
     * @param int $widthRow
     *
     * @return string
     */
    public function format(array $data, int $heightCol, int $widthRow) : string
    {
        $widthCol = $this->getWidthCol($data);
        $size = $this->getColSize($data, $heightCol, $widthCol, $widthRow);

        return $this->getOutput(array_chunk($data, $size), $widthCol);
    }

    /**
     * @param array $data
     * @param int $heightCol
     * @param int $widthCol
     * @param int $widthScreen
     *
     * @return int
     */
    protected function getColSize(array $data, int $heightCol, int $widthCol, int $widthScreen) : int
    {
        // try to perform with client settings
        $count = (int)ceil(count($data) / $heightCol);
        $availableWidth = (int)(ceil(($widthScreen - $this->padding) / $count) - $this->padding);

        $isCanBePlaced = $availableWidth >= $widthCol;

        if ($isCanBePlaced) {
            return $heightCol;
        } else {
            // otherwise using auto calculation
            $count = (int)floor(($widthScreen - $this->padding) / $widthCol);

            return ceil(count($data) / $count);
        }
    }

    /**
     * @param array $data
     * @param int $chunks
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
     * @param int $offset
     *
     * @return string
     */
    protected function getOutput(array $data, int $offset) : string
    {
        $offset = $this->padding + $offset;

        $output = PHP_EOL;
        $i = 0;

        while (true) {

            foreach ($data as $column) {

                if (!isset($column[$i])) {

                    if (isset($data[0][$i + 1])) {
                        $value = '';
                    } else {
                        break 2;
                    }
                } else {
                    $value = $column[$i];
                }

                $output .= $value . $this->getSpace($offset - strlen($value));
            }

            $i++;
            $output .= PHP_EOL;
        }

        return $output . PHP_EOL;
    }

    /**
     * @param int $n
     *
     * @return string
     */
    protected function getSpace(int $n) : string
    {
        return implode('', array_fill(0, $n, ' '));
    }

}