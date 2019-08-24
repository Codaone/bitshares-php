<?php

namespace Codaone\Bitshares\Components;

class Price extends Object
{
    /** @var Amount */
    private $amount;

    /** @var Asset */
    private $quote;

    /** @var Asset */
    private $base;

    public function __toString()
    {
        return sprintf('%s %s/%s', $this->amount->getAmount(), $this->base->getSymbol(), $this->quote->getSymbol());
    }
}