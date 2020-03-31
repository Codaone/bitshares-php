<?php

namespace Codaone\BitShares\Component;

use Codaone\BitShares\BitShares;
use Codaone\BitShares\Component\Base\Object;

/**
 * Class Asset
 * @package Codaone\BitShares\Component
 *
 * @method getId
 * @method string getSymbol
 * @method string|integer getPrecision
 */
class Asset extends Object
{
    public function __construct($asset)
    {
        $cache = $this->getCacheData('asset', $asset);
        if (!$cache) {
            $bitShares = new BitShares();
            if (!count(explode('.', $asset)) == 3) {
                $asset = $bitShares->getAssetIdFromString($asset);
            }
            $assetData = $bitShares->getAssets([$asset]);
            $this->setData($assetData[0]);
            // cache with id and symbol
            $this->setCacheData('asset', $this->getId(), $assetData[0]);
            $this->setCacheData('asset', $this->getSymbol(), $assetData[0]);
        } else {
            $this->setData($cache);
        }
    }

    /**
     * @return Account
     */
    public function getIssuer()
    {
        $issuerId = $this->getData('issuer');
        $issuer   = new Account($issuerId);
        return $issuer;
    }

    /**
     * @example BTS USD OPEN.BTC
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getSymbol();
    }
}