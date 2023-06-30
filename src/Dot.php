<?php

namespace LTDBeget\Yiiic;

class Dot implements \ArrayAccess
{

    const SEPARATOR = '.';

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __debugInfo()
    {
        return $this->data;
    }

    public function __toString()
    {
        return (string)var_export($this->data);
    }

    public function offsetExists($offset)
    {
        return $this->offsetGet($offset) !== NULL;
    }

    public function offsetGet($offset)
    {
        $segments = self::parseOffset($offset);
        $current  = $this->data;

        while (($k = array_shift($segments)) !== NULL) {

            if ($this->keyExists($k, $current)) {
                $current = $current[$k];
            } else {
                return NULL;
            }
        }

        return $current;
    }

    public function offsetSet($offset, $value)
    {
        $segments = self::parseOffset($offset);
        $current  = &$this->data;
        $length   = count($segments);

        $i = 0;
        while ($length-- > 0) {
            $k = $segments[$i++];

            if (is_array($current)) {

                if (!array_key_exists($k, $current)) {
                    $current[$k] = [];
                }

                $current = &$current[$k];
            } else {
                $scalarKey = implode('.', array_slice($segments, 0, --$i));
                throw new \InvalidArgumentException(sprintf('invalid key, %s is scalar, expected array', $scalarKey));
            }
        }

        $current = $value;
    }

    public function offsetUnset($offset)
    {
        $segments = self::parseOffset($offset);
        $current  = &$this->data;
        $length   = count($segments);

        while ($length-- > 1) {
            $k = array_shift($segments);

            if (!$this->keyExists($k, $current)) {
                return;
            }

            $current = &$current[$k];
        }

        if ($this->keyExists($segments[0], $current)) {
            unset($current[$segments[0]]);
        }
    }

    protected function keyExists($key, $array)
    {
        return is_array($array) && array_key_exists($key, $array);
    }

    protected static function parseOffset($offset)
    {
        if ($offset === '') {
            return [];
        }

        return explode(self::SEPARATOR, $offset);
    }

}