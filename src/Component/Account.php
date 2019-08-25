<?php

namespace Codaone\BitShares\Component;

use Codaone\BitShares\BitShares;

/**
 * Class Account
 * @package Codaone\BitShares\Component
 *
 * @method getId
 * @method getName
 */
class Account extends Object
{
    private $full;

    /**
     * Account constructor.
     * @param      $accountName
     * @param bool $loadFullData
     */
    public function __construct($accountName, $loadFullData = false)
    {
        $bitShares = new BitShares();
        if($loadFullData) {
            $fullData = $bitShares->getFullAccounts([$accountName], false);
            $accountData = $fullData[0][1]['account'];
            unset($fullData[0][1]['account']);
            $mergedData = array_merge($fullData[0][1], $accountData);
            $this->setData($mergedData);
            $this->full = true;
        } else {
            $data = $bitShares->lookupAccountNames([$accountName]);
            $this->setData($data[0]);
            $this->full = false;
        }
    }

    /**
     * @return Object|Order[]
     */
    public function getOpenOrders() {
        $this->ensureFull();
        $orders = $this->getData('limit_orders');
        foreach ($orders as &$o) {
            $o = new Order($o);
        }
        $dataObject = new Object();
        $dataObject->setData($orders);
        return $dataObject;
    }

    /**
     * @return Object|Amount[]
     */
    public function getBalances() {
        $this->ensureFull();
        $balances = $this->getData('balances');
        foreach($balances as &$b) {
            $b = new Amount($b['amount'], $b['id']);
        }
        $dataObject = new Object();
        $dataObject->setData($balances);
        return $dataObject;
    }

    /**
     * @param $asset string|Asset
     * @return false|Amount
     */
    public function getBalance($asset) {
        if(!$asset instanceof Asset) {
            $asset = new Asset($asset);
        }
        $balances = $this->getBalances();
        foreach ($balances as $b) {
            if($b->getAsset()->getId() == $asset->getId()) {
                return $b;
            }
        }
        return false;
    }

    /**
     * @return Object|array
     */
    public function getHistory() {
        $bitShares = new BitShares();
        // @todo maybe history api calls should be easier
        $history = $bitShares->call("history", "get_account_history", [$this->getId(), "1.11.0", 100, "1.11.-1"]);
        $dataObject = new Object();
        $dataObject->setData($history);
        return $dataObject;
    }

    private function ensureFull() {
        if(!$this->full) {
            $this->__construct($this->getName(), true);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}
