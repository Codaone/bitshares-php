<?php

namespace Codaone\BitShares\Component;

/**
 * Class Order
 * @package Codaone\BitShares\Component
 *
 * @method Amount getForSale
 * @method getId
 */
class Order extends Price
{
    /**
     * @todo handle Filled Orders (or in different class?)
     * Order constructor.
     * @param array|string|float $orderPrice
     * @param Amount|null $base
     * @param Amount|null $quote
     */
    public function __construct($orderPrice, Amount $base = null, Amount $quote = null)
    {
        if(is_array($orderPrice)) {
            $this->setData($orderPrice);
            if($this->getData('for_sale')) {
                $this->setData("for_sale", new Amount($this->getForSale(), $this->getData('sell_price/base/asset_id'), false));
                $this->base = new Amount($this->getData('sell_price/base/amount'), $this->getData('sell_price/base/asset_id'), false);
                $this->quote = new Amount($this->getData('sell_price/quote/amount'), $this->getData('sell_price/quote/asset_id'), false);
                parent::__construct($this->getBase(), $this->getQuote());
            }
        } else {
            $this->base = $base;
            $this->quote = $quote;
            parent::__construct($this->getBase(), $this->getQuote(), $orderPrice);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getQuote() . ' for ' . $this->getBase() . ' @' . parent::__toString();
    }
}
