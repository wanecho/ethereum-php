<?php
/**
 * author: NanQi
 * datetime: 2019/7/2 16:07
 */

namespace Ethereum;

use InvalidArgumentException;
use Web3p\EthereumTx\Transaction;

class ERC20 extends Eth {

    protected $contractAddress;

    function __construct(string $contractAddress, ProxyApi $proxyApi) {
        parent::__construct($proxyApi);

        $this->contractAddress = $contractAddress;
    }

    public function balanceByApi(string $address, int $decimals)
    {
        if ($this->proxyApi instanceof EtherscanApi) {
            $res = $this->proxyApi->send('tokenbalance', [
                'module' => 'account',
                'contractaddress' => $this->contractAddress,
                'address' => $address
            ]);

            if ($res !== false) {
                return Utils::toDisplayAmount($res, $decimals);
            } else {
                return false;
            }
        } else {
            throw new InvalidArgumentException('type invalid');
        }

    }

    public function balance(string $address, int $decimals = 16)
    {
        $params = [];
        $params['to'] = $this->contractAddress;

        $method = 'balanceOf(address)';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($address);

        $params['data'] = "0x{$formatMethod}{$formatAddress}";

        $balance = $this->proxyApi->send('eth_call', [$params,"latest"]);
        return Utils::toDisplayAmount($balance, $decimals);
    }

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = 'standard',int $decimals=8)
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        //dd($this->proxyApi->gasPrice());
        // if (!Utils::isHex($gasPrice)) {
        //     echo $gasPrice = Utils::toHex(self::gasPriceOracle($gasPrice), true);
        // }
        $gasPrice = $this->proxyApi->gasPrice();
        echo Utils::toDisplayAmount(hexdec($gasPrice),18);
        echo Utils::toDisplayAmount(hexdec($gasPrice),18)*hexdec(0xea60);

        $params = [
            'nonce' => "$nonce",
            'from' => $from,
            'to' => $this->contractAddress,
            'gas' => '0xea60',
            'gasPrice' => "$gasPrice",
            'value' => Utils::NONE,
            'chainId' => self::getChainId($this->proxyApi->getNetwork()),
        ];
        $val = Utils::toMinUnitByDecimals("$value",$decimals);

        $method = 'transfer(address,uint256)';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($to);
        $formatInteger = Formatter::toIntegerFormat($val);

        $params['data'] = "0x{$formatMethod}{$formatAddress}{$formatInteger}";
        $transaction = new Transaction($params);

        $raw = $transaction->sign($privateKey);
        $res = $this->proxyApi->sendRawTransaction('0x'.$raw);
        if ($res !== false) {
            $this->emit(new TransactionEvent($transaction, $privateKey, $res));
        }

        return $res;
    }
}