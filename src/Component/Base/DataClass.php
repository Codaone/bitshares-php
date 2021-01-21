<?php

namespace Codaone\BitShares\Component\Base;

/**
 * Class Object
 * @package Codaone\BitShares\Component
 */
class DataClass implements \ArrayAccess, \Iterator
{
    /**
     * @var array
     */
    private $_data = [];

    /** @var array */
    private static $_underscoreCache;

    /** @var self[] */
    private static $_dataCache;

    protected function setCacheData($bucket, $key, $value = null)
    {
        if (!isset(self::$_dataCache[$bucket])) {
            self::$_dataCache[$bucket] = new self();
        }
        self::$_dataCache[$bucket]->setData($key, $value);
    }

    protected function getCacheData($bucket, $key)
    {
        if (isset(self::$_dataCache[$bucket])) {
            return self::$_dataCache[$bucket]->getData($key);
        }
        return null;
    }

    /**
     * @param mixed|array $key
     * @param null        $value
     */
    public function setData($key, $value = null)
    {
        if ($key === (array)$key) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
    }

    /**
     * @param null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->setData([]);
        } elseif (is_string($key)) {
            if (isset($this->_data[$key]) || array_key_exists($key,
                    $this->_data)) {
                unset($this->_data[$key]);
            }
        } elseif ($key === (array)$key) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }
        return $this;
    }

    /**
     * @param string $key
     * @param null   $index
     * @return array|mixed|null
     */
    public function getData($key = '', $index = null)
    {
        if ('' === $key) {
            return $this->_data;
        }

        /* process a/b/c key as ['a']['b']['c'] */
        if (strpos($key, '/')) {
            $data = $this->getDataByPath($key);
        } else {
            if (isset($this->_data[$key])) {
                $data = $this->_data[$key];
            } else {
                $data = null;
            }
        }

        if ($index !== null) {
            if ($data === (array)$data) {
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif ($data instanceof DataClass) {
                $data = $data->getData($index);
            } else {
                $data = null;
            }
        }
        return $data;
    }

    /**
     * @param $path
     * @return array|mixed|null
     */
    public function getDataByPath($path)
    {
        $keys = explode('/', $path);

        $data = $this->_data;
        foreach ($keys as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof DataClass) {
                $data = $data->getData($key);
            } else {
                return null;
            }
        }
        return $data;
    }

    public function toJson()
    {
        return json_encode($this->_data);
    }

    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key   = $this->_underscore(substr($method, 3));
                $index = isset($args[0]) ? $args[0] : null;
                return $this->getData($key, $index);
            case 'set':
                $key   = $this->_underscore(substr($method, 3));
                $value = isset($args[0]) ? $args[0] : null;
                $this->setData($key, $value);
        }
    }

    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        $result = strtolower(trim(preg_replace('/([A-Z]|[0-9]+)/', "_$1", $name), '_'));
        self::$_underscoreCache[$name] = $result;
        return $result;
    }

    /** ArrayAccess stuff */

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    public function offsetExists($offset)
    {
        return isset($this->$offset) || isset($this->_data[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset)
    {
        if (isset($this->$offset)) {
            return $this->$offset;
        } else {
            return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
        }
    }

    /** Iterator stuff */

    public function rewind()
    {
        reset($this->_data);
    }

    public function current()
    {
        $var = current($this->_data);
        return $var;
    }

    public function key()
    {
        $var = key($this->_data);
        return $var;
    }

    public function next()
    {
        $var = next($this->_data);
        return $var;
    }

    public function valid()
    {
        $key = key($this->_data);
        $var = ($key !== null && $key !== false);
        return $var;
    }
}