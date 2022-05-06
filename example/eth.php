<?php

require_once '../vendor/autoload.php';

use Ethereum\Eth;
use Ethereum\BscScanApi;

$api = new BscScanApi('283YK1CHKHDB1122TKFCCQH11BX7R4H7GY');

$eth = new Eth($api);
