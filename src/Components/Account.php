<?php

namespace Codaone\Bitshares\Components;

class Account extends Object
{
    public function getId($accountName)
    {
        $bitShares = new BitShares();
        $account = $bitShares->getLookupAccountNames([$accountName]);
        return reset($account)['id'];
    }

    public function get($accountName)
    {
        $bitShares = new BitShares();
        $accountId = $this->getId($accountName);
        return $bitShares->getFullAccounts([$accountId], false);
    }
}
