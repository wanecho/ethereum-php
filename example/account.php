<?php

require_once '../vendor/autoload.php';

use Ethereum\Eth;
use Ethereum\InfuraApi;
use Ethereum\PEMHelper;
$eth = new Eth(new InfuraApi('d5f9d42ef09845d485ba9520847869e2'));

//$pk = PEMHelper::generateNewPrivateKey();
//var_dump($pk);
$addr =  PEMHelper::privateKeyToAddress("5d4dc0d9e1df3071448cc1262523cbc02d60edad19beebc03d91a19ca9ca4944");
var_dump($addr);