<?php

require_once '../vendor/autoload.php';

use Ethereum\Eth;
use Ethereum\InfuraApi;

$eth = new Eth(new InfuraApi('d5f9d42ef09845d485ba9520847869e2'));

// $ethBalance = $eth->ethBalance('0xE4229fc07b13f94E9D0a03510b496E88a5cf4cb8');
// var_dump($ethBalance);

$gasPrice = $eth->gasPriceOracle();
var_dump($gasPrice);