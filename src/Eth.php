<?php
/**
 * author: NanQi
 * datetime: 2019/7/2 13:49
 */
namespace Ethereum;

use League\Event\EmitterTrait;
use Web3p\EthereumTx\Transaction;

/**
 * @method bool|null receiptStatus(string $txHash)
 * @method mixed gasPrice()
 * @method mixed ethBalance(string $address)
 * @method mixed getTransactionReceipt(string $txHash)
 */
class Eth {
    use EmitterTrait;

    protected $proxyApi;

    function __construct(ProxyApi $proxyApi) {
        $this->proxyApi = $proxyApi;
    }

    function __call($name, $arguments)
    {
        return call_user_func_array([$this->proxyApi, $name], $arguments);
    }

   

    public static function getChainId($network) : int {
        $chainId = 1;
        switch ($network) {
            case 'rinkeby':
                $chainId = 4;
                break;
            case 'ropsten':
                $chainId = 3;
                break;
            case 'kovan':
                $chainId = 42;
                break;
            default:
                break;
        }

        return $chainId;
    }

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = 'Safe')
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        if (!Utils::isHex($gasPrice)) {
            $gasPrice = $this->proxyApi->gasPriceOracle($gasPrice);
            if( $gasPrice === false ){
                $gasPrice = $this->proxyApi->gasPrice();
            }
        }
        $eth = Utils::toWei("$value", 'ether');
        $eth = Utils::toHex($eth, true);
        
        $transaction = new Transaction([
            'nonce' => "$nonce",
            'from' => $from,
            'to' => $to,
            'gas' => '0x76c0',
            'gasPrice' => "$gasPrice",
            'value' => "$eth",
            'chainId' => $this->proxyApi->getChainId(),
        ]);
        $raw = $transaction->sign($privateKey);
        
        $res = $this->proxyApi->sendRawTransaction('0x'.$raw);
        // print_r($res);
        // if ($res !== false) {
        //     $this->emit(new TransactionEvent($transaction, $privateKey, $res));
        // }

        return $res;
    }
}