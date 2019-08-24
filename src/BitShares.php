<?php

namespace Codaone\Bitshares;


class BitShares extends Components\Object
{
    public static $bitSharesInstance;

    private $wssClient;
    private $rpcClient;

    public function __construct($node = 'wss://btsws.roelandp.nl/ws')
    {
        if(self::$bitSharesInstance) {
            return self::$bitSharesInstance;
        } else {
            $clientConfig    = new \WSSC\Components\ClientConfig();
            $this->rpcClient = new \Datto\JsonRpc\Client();
            try {
                $this->wssClient = new \WSSC\WebSocketClient($node,
                    $clientConfig);
            } catch (\Exception $e) {
                throw new $e;
            }
            self::$bitSharesInstance = $this;
            return $this;
        }
    }

    public function __call($method, $args)
    {
        $method = $this->_underscore($method);
        $rpcData = $this->getRpcRequest($method, $args);
        $result = $this->getWssResponse($rpcData);
        $response = $this->getResponse($result);
        return $response;
    }

    private function getRpcRequest($method, $data)
    {
        $this->rpcClient->reset();
        $this->rpcClient->query(1, $method, $data);
        return $this->rpcClient->encode();
    }

    private function getWssResponse($rpcData)
    {
        $this->wssClient->send($rpcData);
        $result = $this->wssClient->receive();
        return $this->getResponse($result);
    }

    private function getResponse($data)
    {
        $array = json_decode($data, true);
        if (isset($array['result'])) {
            return $array['result'];
        } else {
            return $array;
        }
    }
}
