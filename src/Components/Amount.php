<?php

namespace Codaone\Bitshares\Components;

class Amount extends Object
{
    /** @var float|int */
    private $amount;

    /** @var Asset */
    private $asset;

    public function getAmount(){
        return $this->amount;
    }

    public function getPrecision() {
        return $this->asset->getPrecision();
    }

    public function getSymbol(){
        return $this->asset->getSymbol();
    }
}