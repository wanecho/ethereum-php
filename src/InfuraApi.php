<?php
/**
 * author: NanQi
 * datetime: 2019/7/3 17:53
 */
namespace Ethereum;

class InfuraApi implements ProxyApi {

    protected $apiKey;
    protected $network;

    function __construct(string $apiKey,$network = 'mainnet') {
        $this->apiKey = $apiKey;
        $this->network = $network;
    }

    public function send($method, $params = [])
    {
        $url = "https://mainnet.infura.io/v3/{$this->apiKey}";
        $strParams = is_array($params)?json_encode($params):'"'.$params.'"';
        $data_string = '{"jsonrpc":"2.0","method":"'.$method.'","params": '.$strParams.',"id":1}';
        $res = Utils::httpRequest('POST', $url, [
            'body' => $data_string
        ]);
        if (isset($res['result'])) {
            return $res['result'];
        }else{
            return false;
        }
        
    }

    function gasPrice()
    {
        return $this->send('eth_gasPrice');
    }

    function ethBalance(string $address)
    {
        // TODO: Implement balance() method.
        $retDiv = Utils::fromWei($this->send('eth_getBalance',[$address,'latest']), 'ether');
        if (is_array($retDiv)) {
            return Utils::divideDisplay($retDiv, 18);
        } else {
            return $retDiv;
        }
    }

    /**
     * 返回有关给定哈希值的交易状态
     */
    function receiptStatus(string $txHash): ?bool
    {
        $res = $this->send('eth_getTransactionByHash', [$txHash]);
        if (!$res) {
            return false;
        }

        if (!$res['blockNumber']) {
            return null;
        }

        $res =  $this->send('eth_getTransactionReceipt',[$txHash]);
        return hexdec($res['status']) == '1';
    }

    function sendRawTransaction($raw)
    {   
        $res = $this->send('eth_sendRawTransaction',[$raw]);
        return $res;
    }

    function getNonce(string $address)
    {
        return $this->send('eth_getTransactionCount', [$address,"latest"]);
    }

    function getTransactionReceipt(string $txHash)
    {
        // TODO: Implement getTransactionReceipt() method.
    }

    function getNetwork(): string
    {
        return $this->network;
    }

    function ethCall($params)
    {
        return $this->send('eth_call',[$params,"latest"]);
    }

    /**
     *  type:Safe,Propose,Fast
     */
    public function gasPriceOracle($type="Safe"){

        $res = $this->send('gasoracle', ['module' => 'gastracker']);
        $type = $type."GasPrice";
        if (isset($res['result'][$type])) {
            $price = Utils::toWei($res['result'][$type], 'gwei');
            return Utils::toHex($price,true);
        } else {
            return false;
        }

    }

}
