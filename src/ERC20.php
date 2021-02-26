<?php
/**
 * author: NanQi
 * datetime: 2019/7/2 16:07
 */

namespace Ethereum;

use InvalidArgumentException;
use Web3p\EthereumTx\Transaction;
use Ethereum\Utils;

class ERC20 extends Eth {

    protected $contractAddress;

    function __construct(string $contractAddress, ProxyApi $proxyApi) {
        parent::__construct($proxyApi);

        $this->contractAddress = $contractAddress;
    }

    
    public function name():string
    {
        $params['to'] = $this->contractAddress;
        $method = "name()";
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($this->contractAddress);
        $params['data'] = "0x{$formatMethod}{$formatAddress}";
        $result = $this->proxyApi->send('eth_call', [$params,"latest"]);
        $name =  trim(Utils::hexToBin($result));
        if (!is_string($name)) {
            throw new InvalidArgumentException('Failed to retrieve ERC20 token name');
        }
        return $name;
    }

    public function symbol(): string
    {
        $params['to'] = $this->contractAddress;
        $method = 'symbol()';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($this->contractAddress);
        $params['data'] = "0x{$formatMethod}{$formatAddress}";
        $result = $this->proxyApi->send('eth_call', [$params,"latest"]);
        $symbol = trim(Utils::hexToBin($result)) ?? null;
        if (!is_string($symbol)) {
            throw new InvalidArgumentException('Failed to retrieve ERC20 token symbol');
        }
        return $symbol;
    }

    public function decimals()
    {
        $params['to'] = $this->contractAddress;
        $method = 'decimals()';
        $formatMethod = Formatter::toMethodFormat($method);
        $formatAddress = Formatter::toAddressFormat($this->contractAddress);
        $params['data'] = "0x{$formatMethod}{$formatAddress}";
        $result = $this->proxyApi->send('eth_call', [$params,"latest"]);
        $decimals = intval(Utils::toBn($result)->toString() ?? null);
        if (!is_int($decimals)) {
            throw new InvalidArgumentException('Failed to retrieve ERC20 token decimals value');
        }
        return $decimals;
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
        if (!Utils::isHex($gasPrice)) {
            $gasPrice = Utils::toHex(self::gasPriceOracle($gasPrice), true);
            if( $gasPrice === false ){
                $gasPrice = $this->proxyApi->gasPrice();
            }
        }
        
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