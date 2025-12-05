<?php

namespace App\Service\Api;

use App\Service\BaseService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Hash;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redis;
use Icharle\Wxtool\Wxtool;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Web3p\EthereumTx\Transaction;
use Web3p\RLP\RLP;
use Web3\Web3;
use Web3\Contract;
use App\Service\Eth\Callback;

class TradeService extends BaseService
{
    /**
     * 投注
     * @Author   Chen
     */
    public function deposit($request)
    {
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        if(!$user){
            $this->errorMsg('未找到用户');
        }
        // $this->getPepePrice();
        // $priceKey = env('APP_NAME').'_pepe_price';
        // $pepe_price = Redis::exists($priceKey) ? Redis::get($priceKey) : 0;
        // if(bcmul($pepe_price, bcpow(10,8), 0) <= 0){
        //     $this->errorMsg('投注失败，获取价格失败');
        // }
        // dump($pepe_price);
        // dump(bcdiv($usdt_amount, $pepe_price, 8));
        switch ($request['deposit_type']) {
            case 1: // USDT
                if($request['deposit_amount'] > $user['usdt']){
                    $this->errorMsg('USDT余额不足');
                }
                $amount = App::make('tradeService')->checkUsdtPrice($request['deposit_amount']);
                $usdt_amount = $request['deposit_amount'];
                $wbsc_price = App::make('tradeService')->checkWbscPrice(1);
                break;
            case 2: // WBSC
                if($request['deposit_amount'] > $user['wbsc']){
                    $this->errorMsg('WBSC余额不足');
                }
                $usdt_amount = App::make('tradeService')->checkWbscPrice($request['deposit_amount']);
                $amount = $request['deposit_amount'];
                $wbsc_price = bcdiv($usdt_amount, $amount, 8);
                break;
        }
        // dump($amount);
        // dd($usdt_amount);
        if((float)$request['deposit_amount'] <= 0){
            $this->errorMsg('投注数量有误');
        }
        if($usdt_amount < 10){
            $this->errorMsg('最低起投为10 USDT');
        }
        $deposit = App::make('deposit')->where('address',$user['address'])->orderBy('deposit_id','desc')->first();
        if(!empty($deposit) && $deposit['amount'] > $amount){
            $this->errorMsg('最低起投为'.$deposit['amount']);
        }
        $basicSet = App::make('setting')->getItem('basic');
        $profit_amount = bcmul($amount, (1 + ($basicSet['static_ratio'] / 100)), 6);
        $lock_key = 'lock_deposit_' . $user['address'];
        // 加锁
        $is_lock = Redis::setnx($lock_key, 1);
        // 获取锁权限
        if($is_lock == true){
            // 设置锁时长
            Redis::setex($lock_key, 10, 1);
        }else{
            $this->errorMsg('操作频繁，请稍后再试');
        }
        \DB::beginTransaction();
        try {
            // 更新用户
            $user->is_deposit = 1;
            $user->personal_amount += $usdt_amount;
            $user->total_deposit_amount += $amount;
            $user->total_deposit_profit += $profit_amount;
            $user->save();
            $directUser = $user['up_address'] ? App::make('user')->details(['address'=>$user['up_address']]) : [];
            if(!empty($directUser)){
                if($directUser['direct_num'] < 10){
                    $is_direct_num = 0;
                    $staticAmount = bcdiv(bcmul($amount, (1 + ($basicSet['static_ratio'] / 100)), 6), 400, 6);
                    $direct_profit = bcmul($staticAmount, ($basicSet['min_direct_ratio'] / 100), 6);
                    $total_direct_profit = bcmul($direct_profit, 400, 6);
                }else{
                    $is_direct_num = 1;
                    $staticAmount = bcdiv(bcmul($amount, (1 + ($basicSet['static_ratio'] / 100)), 6), 400, 6);
                    $direct_profit = bcmul($staticAmount, ($basicSet['max_direct_ratio'] / 100), 6);
                    $total_direct_profit = bcmul($direct_profit, 200, 6);
                }
            }else{
                $is_direct_num = 0;
                $direct_profit = 0;
                $total_direct_profit = 0;
            }
            // 保存投注
            $deposit = App::make('deposit')->create([
                'address'=>$user['address'],
                'amount'=>$amount,
                'wbsc_price'=>$wbsc_price,
                'usdt_amount'=>$usdt_amount,
                'deposit_type'=>$request['deposit_type'],
                'profit_amount'=>$profit_amount,
                'is_direct_num'=>$is_direct_num,
                'total_direct_profit'=>$total_direct_profit,
                'direct_profit'=>$direct_profit,
            ]);
            // 结算
            $deposit->settle();
            // 更新钱包
            $wallet_type = ($request['deposit_type'] == 1) ? 'usdt' : 'wbsc';
            $deposit_amount = ($request['deposit_type'] == 1) ? $usdt_amount : $amount;
            Factory::UserWalletLogService()->add($user['address'], $wallet_type, 1, 204, $deposit_amount,$amount);
            \DB::commit();
            return $this->success('提交成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->errorMsg($e->getMessage());
        }
        return $this->success('获取成功',$data);
    }

    public function getPepePrice()
    {
        try {
            $abi = '[{"inputs":[{"internalType":"address","name":"initialOwner","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"target","type":"address"}],"name":"AddressEmptyCode","type":"error"},{"inputs":[],"name":"AddressFailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"AddressInsufficientBalance","type":"error"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"depositPepe","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"depositUsdt","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"OnlyEOA","type":"error"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"OwnableInvalidOwner","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"OwnableUnauthorizedAccount","type":"error"},{"inputs":[],"name":"SafeERC20FailedCall","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"price","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"value","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"DepositPepe","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"DepositUsdt","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setDepositAddr","outputs":[],"stateMutability":"nonpayable","type":"function"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"user","type":"address"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"SetDepositAddr","type":"event"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"_IPancakeV3Factory","outputs":[{"internalType":"contractIPancakeV3Factory","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"_ISwapRouter","outputs":[{"internalType":"contractISwapRouter","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"depositAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getPriceV2","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"tokenIn","type":"address"},{"internalType":"address","name":"tokenOut","type":"address"},{"internalType":"uint24","name":"fee","type":"uint24"}],"name":"getPriceV3","outputs":[{"internalType":"uint256","name":"price","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"PEPE","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"USDT","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"}]';
            $web3Url = env('PRC_URL');
            $contract = new Contract($web3Url, $abi);
            $this->walletAddress = env('FORMAL_ADDRESS');
            $method = 'getPriceV3';
            $token0 = '0x25d887Ce7a35172C62FeBFD67a1856F20FaEbB00';
            $token1 = '0x55d398326f99059fF775485246999027B3197955';
            $fee = '10000';
            $cb = new Callback();
            $datas = $contract->at($this->walletAddress)->call($method, $token0, $token1, $fee, function($err, $data) use ($cb){
                $price = gmp_strval($data['price']->value);
                $cb->price = $price ? bcdiv($price, bcpow(10,18), 18) : 0;
            });
            $priceKey = env('APP_NAME').'_pepe_price';
            if(bcmul($cb->price, bcpow(10,8), 0) > 0){
                Redis::set($priceKey,$cb->price);
            }
        } catch (\Exception $e) {
            dd('获取价格异常：'.$e->getMessage());
        }
    }
}
