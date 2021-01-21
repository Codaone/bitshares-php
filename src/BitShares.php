<?php

namespace Codaone\BitShares;

use Codaone\BitShares\Exceptions\BitSharesException;

/**
 * Class BitShares
 * @package Codaone\BitShares
 *
 * @method cancelAllSubscriptions($args)
 * @method get24Volume($asset1, $asset2)
 * @method getAccountBalances($args)
 * @method getAccountByName($args)
 * @method getAccountCount($args)
 * @method getAccountIdFromString($args)
 * @method getAccountLimitOrders($args)
 * @method getAccountReferences($args)
 * @method getAccounts($args)
 * @method getAllWorkers($args)
 * @method getAssetCount($args)
 * @method getAssetIdFromString($args)
 * @method getAssets($args)
 * @method getAssetsByIssuer($args)
 * @method getBalanceObjects($args)
 * @method getBlindedBalances($args)
 * @method getBlock($args)
 * @method getBlockHeader($args)
 * @method getBlockHeaderBatch($args)
 * @method getCallOrders($args)
 * @method getCallOrdersByAccount($args)
 * @method getChainId()
 * @method getChainProperties($args)
 * @method getCollateralBids($args)
 * @method getCommitteeCount($args)
 * @method getCommitteeMemberByAccount($args)
 * @method getCommitteeMembers($args)
 * @method getConfig($args)
 * @method getDynamicGlobalProperties($args)
 * @method getFullAccounts(array $args, $args1 = []) array
 * @method getGlobalProperties($args)
 * @method getHtlc($args)
 * @method getHtlcByFrom($args)
 * @method getHtlcByTo($args)
 * @method getKeyReferences($args)
 * @method getLimitOrders($args)
 * @method getMarginPositions($args)
 * @method getNamedAccountBalances($args)
 * @method getObjects($args)
 * @method getOrderBook($asset1, $asset2, $limit)
 * @method getPotentialAddressSignatures($args)
 * @method getPotentialSignatures($args)
 * @method getProposedTransactions($args)
 * @method getRecentTransactionById($args)
 * @method getRequiredFees($args)
 * @method getRequiredSignatures($args)
 * @method getSettleOrders($args)
 * @method getSettleOrdersByAccount($args)
 * @method getTicker($asset1, $asset2)
 * @method getTopMarkets($args)
 * @method getTradeHistory($asset1, $asset2, $stop, $start, $limit)
 * @method getTradeHistoryBySequence($args)
 * @method getTransaction($args)
 * @method getTransactionHex($args)
 * @method getTransactionHexWithoutSig($args)
 * @method getVestedBalances($args)
 * @method getVestingBalances($args)
 * @method getWithdrawPermissionsByGiver($args)
 * @method getWithdrawPermissionsByRecipient($args)
 * @method getWitnessByAccount($args)
 * @method getWitnessCount($args)
 * @method getWitnesses($args)
 * @method getWorkerCount($args)
 * @method getWorkersByAccount($args)
 * @method isPublicKeyRegistered($args)
 * @method listAssets($args)
 * @method lookupAccountNames($args)
 * @method lookupAccounts($args)
 * @method lookupAssetSymbols($args)
 * @method lookupCommitteeMemberAccounts($args)
 * @method lookupVoteIds($args)
 * @method lookupWitnessAccounts($args)
 * @method setAutoSubscription($args)
 * @method setBlockAppliedCallback($args)
 * @method setPendingTransactionCallback($args)
 * @method setSubscribeCallback($args)
 * @method subscribeToMarket($args)
 * @method unsubscribeFromMarket($args)
 * @method validateTransaction($args)
 * @method verifyAccountAuthority($args)
 * @method verifyAuthority($args)
 * @method call($api, $method, $args)
 */
class BitShares extends Component\Base\DataClass
{
    /** @var bool */
    private static $initialized;

    /** @var \WSSC\WebSocketClient */
    private static $wssClient;

    /** @var \Datto\JsonRpc\Client */
    private static $rpcClient;

    /**
     * BitShares constructor.
     * @param string $node
     */
    public function __construct($node = 'wss://btsws.roelandp.nl/ws')
    {
        // @todo add nodelist and find and use first working node
        if (!self::$initialized) {
            $clientConfig    = new \WSSC\Components\ClientConfig();
            self::$rpcClient = new \Datto\JsonRpc\Client();
            try {
                self::$wssClient = new \WSSC\WebSocketClient($node, $clientConfig);
            } catch (\Exception $e) {
                throw new $e;
            }
            self::$initialized = true;
        }
    }

    /**
     * @param $method
     * @param $args
     * @return array|mixed|null
     * @throws BitSharesException
     * @throws \WSSC\Exceptions\BadOpcodeException
     * @throws \WSSC\Exceptions\BadUriException
     * @throws \WSSC\Exceptions\ConnectionException
     */
    public function __call($method, $args)
    {
        $method = $this->_underscore($method);
        $rpcData = $this->getRpcRequest($method, $args);
        $response = $this->getWssResponse($rpcData);
        return $response;
    }

    /**
     * @param $method
     * @param $data
     * @return string|null
     */
    private function getRpcRequest($method, $data)
    {
        self::$rpcClient->reset();
        self::$rpcClient->query(1, $method, $data);
        return self::$rpcClient->encode();
    }

    /**
     * @param $rpcData
     * @return mixed
     * @throws BitSharesException
     * @throws \WSSC\Exceptions\BadOpcodeException
     * @throws \WSSC\Exceptions\BadUriException
     * @throws \WSSC\Exceptions\ConnectionException
     */
    private function getWssResponse($rpcData)
    {
        self::$wssClient->send($rpcData);
        $result = self::$wssClient->receive();
        return $this->getResponse($result);
    }

    /**
     * @param $data
     * @return mixed
     * @throws BitSharesException
     */
    private function getResponse($data)
    {
        $array = json_decode($data, true);
        if (isset($array['result'])) {
            return $array['result'];
        } elseif(isset($array['error'])) {
            throw new BitSharesException($array['error']['message']);
        } else {
            return $array;
        }
    }
}
