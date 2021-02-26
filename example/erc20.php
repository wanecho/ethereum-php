<?php
require_once '../vendor/autoload.php';

use Ethereum\ERC20;
use Ethereum\InfuraApi;
use Ethereum\EtherscanApi;

//$api = new InfuraApi('');
//$api = new EtherscanApi('');
$erc20 = new ERC20('0xdac17f958d2ee523a2206206994597c13d831ec7',$api);

$name = $erc20->name();
var_dump($name);

$symbol = $erc20->symbol();
var_dump($symbol);

$decimals = $erc20->decimals();
var_dump($decimals);

$gasPrice = $api->gasPrice();
var_dump($gasPrice);

$balance = $erc20->balance('',6);
var_dump($balance);

