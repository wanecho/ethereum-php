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

    /**
     *  type:Safe,Propose,Fast
     */
    public static function gasPriceOracle($type = 'Safe')
    {
        $url = 'https://api-cn.etherscan.com/api?module=gastracker&action=gasoracle&apikey=B8IMPU8HAU65HHZDS22X9BYN6IQBHCK961';
        $res = Utils::httpRequest('GET', $url);
        $type = $type."GasPrice";
        if (isset($res['result'][$type])) {
            $price = Utils::toWei($res['result'][$type], 'gwei');
            return Utils::toHex($price,true);
        } else {
            return false;
        }
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

    public function transfer(string $privateKey, string $to, float $value, string $gasPrice = 'standard')
    {
        $from = PEMHelper::privateKeyToAddress($privateKey);
        $nonce = $this->proxyApi->getNonce($from);
        if (!Utils::isHex($gasPrice)) {
            $gasPrice = self::gasPriceOracle($gasPrice);
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
            'chainId' => self::getChainId($this->proxyApi->getNetwork()),
        ]);
        $raw = $transaction->sign($privateKey);
        $res = $this->proxyApi->sendRawTransaction('0x'.$raw);
        if ($res !== false) {
            $this->emit(new TransactionEvent($transaction, $privateKey, $res));
        }

        return $res;
    }
}