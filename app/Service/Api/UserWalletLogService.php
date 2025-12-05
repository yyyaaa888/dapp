<?php

namespace App\Service\Api;

use App\Service\BaseService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class UserWalletLogService extends BaseService
{
    private $modelClass = [
        'usdt' => 'App\Models\UsdtLog',
        'pepe' => 'App\Models\PepeLog',
        'wbsc' => 'App\Models\WbscLog',
    ];

    /**
     * 用户收益明细
     * @Author   Chen
     * @param    [type]     $address      用户地址
     * @param    integer    $wallet_type 账户类型
     * @param    integer    $cashier_type 收支类型：0收入，1支出
     * @param    integer    $log_type     交易类型
     * @param    integer    $amount       交易金额
     * @param    string     $remarks      备注
     */
    public function add($address, $wallet_type, $cashier_type = 0, $log_type = 0, $amount = 0, $remarks = '')
    {
        // 用户信息
        $user = App::make('user')->lockForUpdate()->where('address',$address)->first();
        if(!$user){
            return true;
        }
        if($cashier_type){
            // 支出
            $user->{$wallet_type} -= $amount;
        }else{
            // 收入
            $user->{$wallet_type} += $amount;
        }
        if(($wallet_type == 'usdt' || $wallet_type == 'wbsc') && $log_type == 204){
            $user->is_profit_end = 0;
        }
        if($wallet_type == 'wbsc' && in_array($log_type, [301,302,303,304,307])){
            $user->release_deposit_profit += $amount;
            if($user->release_deposit_profit >= $user->total_deposit_profit){
                $user->is_profit_end = 1; 
            }
        }
        $resUser = $user->save();
        $balance = $user->{$wallet_type};
        // $resBill = (new $this->modelClass[$wallet_type])->create([
        $resBill = App::make('tradeLog')->create([
            'address' => $address,
            'wallet_type' => $wallet_type,
            'log_type' => $log_type,
            'cashier_type' => $cashier_type,
            'amount' => $amount,
            'balance' => $balance,
            'remarks' => $remarks
        ]);
        return ($resUser && $resBill) ? true : false;
    }
}
