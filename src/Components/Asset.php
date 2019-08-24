<?php

namespace Codaone\Bitshares\Components;

class Asset extends Object
{
    /** @var string */
    private $id;

    /** @var string */
    private $symbol;

    /** @var int */
    private $precision;

    public function __construct($string)
    {
        $bitShares = new BitShares();
        $asset = $bitShares->getAsset($string);
    }

    /**
     * @return string
     */
    public function getSymbol() {
        return $this->symbol;
    }

    /**
     * @return int
     */
    public function getPrecision() {
        return $this->precision;
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @example BTS USD OPEN.BTC
     * @return string
     */
    public function __toString()
    {
        return $this->getSymbol();
    }
}