<?php
require_once '../vendor/autoload.php';

use Ethereum\ERC20;
use Ethereum\InfuraApi;

$api = new InfuraApi('d5f9d42ef09845d485ba9520847869e2');
$erc20 = new ERC20('0xdac17f958d2ee523a2206206994597c13d831ec7',$api);

// $name = $erc20->name();
// var_dump($name);

// $symbol = $erc20->symbol();
// var_dump($symbol);

// $decimals = $erc20->decimals();
// var_dump($decimals);

$gasPrice = $api->gasPrice();
var_dump($gasPrice);
// $balance = $erc20->balance('0xE4229fc07b13f94E9D0a03510b496E88a5cf4cb8',6);
// var_dump($balance);

