BitShares PHP Library
========

Overview
---
This package allows you to read from BitShares network from selected BitShares node. 
Package does not contain signing operations so any operation which needs signing does not work.


Installation
---
```
composer require codaone/bitshares-php
```

Requirements
---
* PHP >= 7.0

Examples
===
BitShares class
---
All methods are passed as rpc meh

```php
$bitShares = new BitShares('wss://node.com');
$block = $bitShares->getBlock('40385973');
$bitShares->getChainId();
```

Getting data from named api
```php
$bitShares = new BitShares('wss://node.com');
$block = $bitShares->call('history', 'method_name', ['param1', 'param2']);
```


Account
---
```php
$account = new Account('account-name');
$openorders = $account->getOpenOrders();
foreach($openorders as $order) {
...
}
```

Market
---
```php
$market = new Market('BTS/USD'); // delimiter can also be : _ -
$market->getVolume24h('BTS')->getAmount();
$market->getTicker();
$market->getOrderBook(25)->getAsks();
```

Asset
---
```php
$asset = new Asset('BTS');
$asset->getId(); // 1.3.0
$asset->getPrecision(); // 5
```

General
---
Every class under Component namespace extends Object class which is iterable and has arrayAccess.
This means that for example these are possible:

```php
$market = new Market('BTS/USD');
$market->getBase()->getSymbol(); // BTS
$market['base']['symbol]; // BTS
```

```php
$account = new Account('account-name');
$account->getData('owner/weight_threshold');
$account['owner']['weight_threshold'];
$account->getBalances(); // returns balances array
$account->getData('balances/0/asset_type');
$account['balances'][0]['asset_type'];
foreach($account as $key => $value) {
...
}
```

Contributing
---
Feel free to open pull requests or add an issue

License
---
A copy of the license is available in the repository's [LICENSE](LICENSE.txt) file.