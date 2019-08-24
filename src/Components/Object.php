<?php

namespace Codaone\Bitshares\Components;

class Object
{
    private $_data = [];

    /** @var array */
    private static $_underscoreCache;

    /** @var self[] */
    private static $_dataCache;

    public function setCacheData($bucket, $key, $value = null) {
        if(!isset(self::$_dataCache[$bucket])) {
            self::$_dataCache[$bucket] = new self();
        }
        self::$_dataCache[$bucket]->setData($key, $value);
    }

    public function getCacheData($bucket, $key){
        if(isset(self::$_dataCache[$bucket])) {
            self::$_dataCache[$bucket]->getData($key);
        }
        return null;
    }

    public function setData($key, $value = null) {
        if ($key === (array)$key) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
    }

    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->setData([]);
        } elseif (is_string($key)) {
            if (isset($this->_data[$key]) || array_key_exists($key, $this->_data)) {
                unset($this->_data[$key]);
            }
        } elseif ($key === (array)$key) {
            foreach ($key as $element) {
                $this->unsetData($element);
            }
        }
        return $this;
    }

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
            }
            $data = null;
        }

        if ($index !== null) {
            if ($data === (array)$data) {
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif (is_string($data)) {
                $data = explode(PHP_EOL, $data);
                $data = isset($data[$index]) ? $data[$index] : null;
            } elseif ($data instanceof Object) {
                $data = $data->getData($index);
            } else {
                $data = null;
            }
        }
        return $data;
    }

    public function getDataByPath($path)
    {
        $keys = explode('/', $path);

        $data = $this->_data;
        foreach ($keys as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof Object) {
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
}