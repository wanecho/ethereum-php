<?php
/**
 * author: Wan8899
 * datetime: 2022/4/18 23:00
 */

namespace Ethereum;

class BscScanApi implements ProxyApi {
    protected $apiKey;
    protected $network;

    function __construct(string $apiKey, $network = 'mainnet') {
        $this->apiKey = $apiKey;
        $this->network = $network;
    }

    public  function getChainId() : int {
        $chainId = 56 ;
        return $chainId;
    }

    public function send($method, $params = [])
    {
        $defaultParams = [
            'module' => 'proxy',
            'tag' => 'latest',
        ];
        foreach ($defaultParams as $key => $val) {
            if (!isset($params[$key])) {
                $params[$key] = $val;
            }
        }

        $preApi = 'api';
        if ($this->network != 'mainnet') {
            $preApi .= '-' . $this->network;
        }

        $url = "https://$preApi.bscscan.com/api?action={$method}&apikey={$this->apiKey}";
        if ($params && count($params) > 0) {
            $strParams = http_build_query($params);
            $url .= "&{$strParams}";
        }
        $res = Utils::httpRequest('GET', $url);
        if (isset($res['result'])) {
            return $res['result'];
        } else {
            return $res;
        }
    }

    /**
     *  type:Safe,Propose,Fast
     */
    public function gasPriceOracle($type="Safe"){

        $res = $this->send('gasoracle', ['module' => 'gastracker']);
        $type = $type."GasPrice";
        if (isset($res[$type])) {
            $price = Utils::toWei($res[$type], 'gwei');
            return Utils::toHex($price,true);
        } else {
            return false;
        }

    }

    public function gasPrice()
    {
        return $this->send('eth_gasPrice');
    }

    public function ethBalance(string $address)
    {
        $params['module'] = 'account';
        $params['address'] = $address;
        $retDiv = Utils::fromWei($this->send('balance', $params), 'ether');
        if (is_array($retDiv)) {
            return Utils::divideDisplay( (array) $retDiv, 18);
        } else {
            return $retDiv;
        }
    }

    public function receiptStatus(string $txHash): ?bool
    {
        $res = $this->send('eth_getTransactionByHash', ['txhash' => $txHash]);
        if (!$res) {
            return false;
        }

        if (!$res['blockNumber']) {
            return null;
        }

        $params['module'] = 'transaction';
        $params['txhash'] = $txHash;

        $res =  $this->send('gettxreceiptstatus', $params);
        return $res['status'] == '1';
    }

    public function getTransactionReceipt(string $txHash)
    {
        $res = $this->send('eth_getTransactionReceipt', ['txhash' => $txHash]);
        return $res;
    }

    public function sendRawTransaction($raw)
    {
        return $this->send('eth_sendRawTransaction', ['hex' => $raw]);
    }

    public function getNonce(string $address)
    {
        return $this->send('eth_getTransactionCount', ['address' => $address]);
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function ethCall($params): string
    {
        return  $this->send('eth_call',$params);
    }

}
