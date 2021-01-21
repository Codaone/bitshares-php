<?php

namespace Codaone\BitShares\Component;
use Codaone\BitShares\Component\Base\DataClass;

/**
 * Class Amount
 * @package Codaone\BitShares\Component
 */
class Amount extends DataClass
{
    /** @var int */
    protected $amount;

    /** @var Asset */
    protected $asset;

    /**
     * Amount constructor.
     * @param integer|float|string $amount
     * @param null                 $asset
     * @param bool                 $amountAsFloat
     */
    public function __construct($amount, $asset = null, $amountAsFloat = true)
    {
        if (!$asset) {
            list($amount, $asset) = explode(" ", $amount);
        }
        if (!$asset instanceof Asset) {
            $this->asset = new Asset($asset);
        } else {
            $this->asset = $asset;
        }
        if ($amountAsFloat) {
            $this->amount = $amount * 10 ** $this->getPrecision();
        } else {
            $this->amount = $amount;
        }
    }

    /**
     * @param bool $asInteger default = false
     * @return float|int|string
     */
    public function getAmount($asInteger = false)
    {
        if($asInteger) {
            return $this->amount;
        } else {
            return $this->amount / 10 ** $this->getPrecision();
        }
    }

    /**
     * @return int|string
     */
    public function getPrecision()
    {
        return $this->asset->getPrecision();
    }

    /**
     * @return string
     */
    public function getSymbol()
    {
        return $this->asset->getSymbol();
    }

    /**
     * @return Asset
     */
    public function getAsset(){
        return $this->asset;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf("%s %s",
            $this->getAmount(),
            $this->getSymbol());
    }
}