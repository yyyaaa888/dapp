<?php

namespace App\Service\Web3p;

use App\Service\BaseService;
use Illuminate\Support\Facades\Log;
use App\Service\Eth\Callback;
use Web3p\EthereumTx\Transaction;
use Web3\Contract;
use Web3\Web3;
use Web3p\RLP\RLP;

class TradeService extends BaseService {

    public $abi = '[{"inputs":[{"internalType":"address","name":"initialOwner","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"OwnableInvalidOwner","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"OwnableUnauthorizedAccount","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"user","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"PayUsdt","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"user","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Paywbsc","type":"event"},{"inputs":[],"name":"USDT","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"amountIn","type":"uint256"},{"internalType":"address","name":"tokenIn","type":"address"},{"internalType":"address","name":"tokenOut","type":"address"}],"name":"checkPrice","outputs":[{"internalType":"uint256","name":"amountOut","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"payUsdt","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"paywbsc","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"receiveAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"router","outputs":[{"internalType":"contractIPancakeRouter","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"token_","type":"address"}],"name":"setToken","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"_receiveAddr","type":"address"}],"name":"setreceiveAddr","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"wbsc","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"}]';

    // wbsc兑换usdt
    public function checkWbscPrice($amount){
        $web3Url = env('PRC_URL');
        $contract = new Contract($web3Url, $this->abi);
        $this->walletAddress = env('RECHARGE_FORMAL_ADDRESS');
        $method = 'checkPrice';
        $wbscAddress = env('WBSC_FORMAL_ADDRESS');
        $usdtAddress = env('USDT_FORMAL_ADDRESS');
        $amount = bcmul(sctonum($amount), bcpow(10,18), 0);
        $cb = new Callback();
        $datas = $contract->at($this->walletAddress)->call($method, $amount, $wbscAddress, $usdtAddress, function($err, $data) use ($cb){
            $usdtValue = gmp_strval($data['amountOut']->value);
            $cb->usdtValue = $usdtValue ? bcdiv($usdtValue, bcpow(10,18), 18) : 0;
        });
        return $cb->usdtValue;
    }

    // usdt兑换wbsc
    public function checkUsdtPrice($amount){
        $web3Url = env('PRC_URL');
        $contract = new Contract($web3Url, $this->abi);
        $this->walletAddress = env('RECHARGE_FORMAL_ADDRESS');
        $method = 'checkPrice';
        $wbscAddress = env('WBSC_FORMAL_ADDRESS');
        $usdtAddress = env('USDT_FORMAL_ADDRESS');
        $amount = bcmul(sctonum($amount), bcpow(10,18), 0);
        $cb = new Callback();
        $datas = $contract->at($this->walletAddress)->call($method, $amount, $usdtAddress, $wbscAddress, function($err, $data) use ($cb){
            $usdtValue = gmp_strval($data['amountOut']->value);
            $cb->usdtValue = $usdtValue ? bcdiv($usdtValue, bcpow(10,18), 18) : 0;
        });
        return $cb->usdtValue;
    }

    public function getNonce($address){
        $url = env('PRC_URL');
        $params['id'] = 1;
        $params['jsonrpc'] = '2.0';
        $params['method'] = 'eth_getTransactionCount';
        $params['params'] = [
            $address,
            'pending'
        ];
        $result = curlPost($url,$params);
        $result = json_decode($result,true);
        if(isset($result['error'])){
            $this->errorMsg($result['error']['message']);
        }
        return $result['result'];
    }

    public  function proxyWithdraw($hex){
        $url = env('PRC_URL');
        $params['id'] = 1;
        $params['jsonrpc'] = '2.0';
        $params['method'] = 'eth_sendRawTransaction';
        $params['params'] = [
            '0x'.$hex
        ];
        $result = curlPost($url,$params);
        $result = json_decode($result,true);
        if(isset($result['error'])){
            $this->errorMsg($result['error']['message']);
        }
        return $result['result'];
    }
}
