<?php

namespace Codaone\BitShares\Component;
use Codaone\BitShares\Component\Base\DataClass;

/**
 * Class Price
 * @package Codaone\BitShares\Component
 */
class Price extends DataClass
{
    /** @var float */
    protected $price;

    /** @var Amount */
    protected $quote;

    /** @var Amount */
    protected $base;

    /**
     * Price constructor.
     * @param string|float|integer|false $price
     * @param Asset|Amount|string   $base
     * @param Asset|Amount|string   $quote
     */
    public function __construct(
        $base,
        $quote,
        $price = false
    ) {
        if($base instanceof Amount && $quote instanceof Amount) {
            $this->base = $base;
            $this->quote = $quote;
            if(!$price) {
                $price = $this->getBase()->getAmount() / $this->getQuote()->getAmount();
            }
        } else {
            if (!$base instanceof Asset) {
                $base = new Asset($base);
            }
            $fraction = $this->fractions($price, 10 ** $base->getPrecision());
            $this->base = new Amount($fraction[0], $base, false);
            $this->quote = new Amount($fraction[1], $quote, false);
        }
        $this->price = $price;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return Amount
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * @return Amount
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * @return $this
     */
    public function invert()
    {
        $tmp = $this->quote;
        $this->quote = $this->base;
        $this->base = $tmp;
        $this->price = 1 / $this->price;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $prec = $this->getBase()->getPrecision();
        return sprintf('%.'.$prec.'f %s/%s',
            $this->getPrice(),
            $this->getBase()->getSymbol(),
            $this->getQuote()->getSymbol()
        );
    }

    /**
     * @param float $v
     * @param int $lim maximum denominator
     * @return array (numerator, denominator)
     */
    private function fractions($v, $lim) {
        if($v < 0) {
            list($n, $d) = $this->fractions(-$v, $lim);
            return array(-$n, $d);
        }
        $z = $lim - $lim;
        list($lower, $upper) = array(array($z, $z+1), array($z+1, $z));
        while(true) {
            $mediant = array(($lower[0] + $upper[0]), ($lower[1] + $upper[1]));
            if($v * $mediant[1] > $mediant[0]) {
                if($lim < $mediant[1])
                    return $upper;
                $lower = $mediant;
            }
            else if($v * $mediant[1] == $mediant[0]) {
                if($lim >= $mediant[1])
                    return $mediant;
                if($lower[1] < $upper[1])
                    return $lower;
                return $upper;
            }
            else {
                if($lim < $mediant[1])
                    return $lower;
                $upper = $mediant;
            }
        }
    }
}
