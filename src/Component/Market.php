<?php

namespace Codaone\BitShares\Component;

use Codaone\BitShares\BitShares;
use Codaone\BitShares\Component\Base\DataClass;

/**
 * Class Market
 * @package Codaone\BitShares\Component
 */
class Market extends DataClass
{
    /** @var Asset */
    protected $base;

    /** @var Asset */
    protected $quote;

    /**
     * Market constructor.
     * @param Asset|string      $marketOrBase
     * @param Asset|string|null $quote
     */
    public function __construct($marketOrBase, $quote = null)
    {
        if(!$quote) {
            list($base, $quote) = preg_split('/[\/\-:_]/', $marketOrBase, 2);
            $this->base = new Asset($base);
            $this->quote = new Asset($quote);
        } else {
            if (!$marketOrBase instanceof Asset) {
                $this->base = new Asset($marketOrBase);
            } else {
                $this->base = $marketOrBase;
            }
            if (!$quote instanceof Asset) {
                $this->quote = new Asset($quote);
            } else {
                $this->quote = $quote;
            }
        }
    }

    /**
     * @return Asset
     */
    public function getBase(){
        return $this->base;
    }

    /**
     * @return Asset
     */
    public function getQuote() {
        return $this->quote;
    }

    /**
     * @param bool $returnAs base/quote
     * @return Amount[]|Amount
     */
    public function getVolume24h($returnAs = false) {
        $bitShares = new BitShares();
        $volume24 = $bitShares->get24Volume(
            $this->getBase()->getId(),
            $this->getQuote()->getId()
        );
        if(!$returnAs) {
            return [
                $this->getBase()->getSymbol() => new Amount($volume24["base_volume"], $this->getBase()),
                $this->getQuote()->getSymbol() => new Amount($volume24["quote_volume"], $this->getQuote()),
            ];
        } elseif(strtolower($returnAs) == 'base') {
            return new Amount($volume24["base_volume"], $this->getBase());
        } elseif(strtolower($returnAs) == 'quote') {
            return new Amount($volume24["quote_volume"], $this->getQuote());
        }
    }

    /**
     * @param int $limit
     * @return DataClass
     */
    public function getOrderBook($limit = 25) {
        $bitShares = new BitShares();
        $orderbook = $bitShares->getOrderBook(
            $this->getBase()->getId(),
            $this->getQuote()->getId(),
            $limit
        );
        $asks = [];
        $bids = [];
        foreach ($orderbook['asks'] as $ask) {
            $asks[] = new Order(
                $ask['price'],
                new Amount($ask["quote"], $this->getQuote()),
                new Amount($ask["base"], $this->getBase())
            );
        }
        foreach ($orderbook['bids'] as $bid) {
            $bids[] = new Order(
                $bid['price'],
                new Amount($bid["quote"], $this->getQuote()),
                new Amount($bid["base"], $this->getBase())
            );
        }
        $data = ["asks" => $asks, "bids" => $bids];
        $dataObject = new DataClass;
        $dataObject->setData($data);
        return $dataObject;
    }

    /**
     * @return DataClass
     */
    public function getTicker() {
        $bitShares = new BitShares();
        $ticker = $bitShares->getTicker(
            $this->getBase()->getId(),
            $this->getQuote()->getId()
        );
        $data = [];
        $data['base_volume'] = new Amount($ticker['base_volume'], $this->getBase());
        $data['quote_volume'] = new Amount($ticker['quote_volume'], $this->getQuote());
        $data['lowest_ask'] = new Price($this->getBase(), $this->getQuote(), $ticker["lowest_ask"]);
        $data['highest_bid'] = new Price($this->getBase(), $this->getQuote(), $ticker["highest_bid"]);
        $data['latest'] = new Price($this->getBase(), $this->getQuote(), $ticker["latest"]);
        $data['percent_change'] = floatval($ticker['percent_change']);
        $dataObject = new DataClass;
        $dataObject->setData($data);
        return $dataObject;
    }

    /**
     * @param int            $limit
     * @param \DateTime|null $start
     * @param \DateTime|null $stop
     * @return DataClass|array
     * @throws \Exception
     */
    public function getTrades($limit = 25, \DateTime $start = null, \DateTime $stop = null) {
        if(!$stop) {
            $stop = new \DateTime();
        }
        if(!$start) {
            $start = new \DateTime();
            $start->modify('-24 hours');
        }
        $bitShares = new BitShares();
        $trades = $bitShares->getTradeHistory(
            $this->getBase()->getSymbol(),
            $this->getQuote()->getSymbol(),
            $stop->format('Y-m-d\TH:i:s'),
            $start->format('Y-m-d\TH:i:s'),
            $limit
        );
        $dataObject = new DataClass;
        $dataObject->setData($trades);
        return $dataObject;
    }

    /**
     * @param Account|string $account
     * @param int $limit
     * @return DataClass|array
     */
    public function getAccountTrades($account, $limit = 25) {
        if(!$account instanceof Account) {
            $account = new Account($account);
        }
        $bitShares = new BitShares();
        // @todo maybe history api calls should be easier
        $tradeHistory = $bitShares->call("history", "get_fill_order_history", [
            $this->getBase()->getId(),
            $this->getQuote()->getId(),
            $limit * 2
        ]);
        $data = [];
        foreach ($tradeHistory as $trade) {
            if($trade['op']['account_id'] == $account->getId()) {
                $data[] = $trade;
            }
        }
        $dataObject = new DataClass;
        $dataObject->setData($data);
        return $dataObject;
    }

    /**
     * @param Account|string $account
     * @return DataClass|array
     */
    public function getAccountOpenOrders($account) {
        if(!$account instanceof Account) {
            $account = new Account($account, true);
        }
        $data = [];
        foreach($account->getOpenOrders() as $order) {
            if((
                    $order->getQuote()->getAsset()->getId() == $this->getQuote()->getId() &&
                    $order->getBase()->getAsset()->getId() == $this->getBase()->getId()
                ) || (
                    $order->getQuote()->getAsset()->getId() == $this->getBase()->getId() &&
                    $order->getBase()->getAsset()->getId() == $this->getQuote()->getId()
                )) {
                $data[] = $order;
            }
        }
        $dataObject = new DataClass;
        $dataObject->setData($data);
        return $dataObject;
    }
}
