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

class UserService extends BaseService
{
    /**
     * 用户信息
     * @Author   Chen
     */
    public function userInfo($request)
    {
        $data['is_user'] = false;
        $data['user_info'] = [];
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        if($user){
            $user = $user->toArray();
            $data['is_user'] = true;
            $directList = App::make('user')->where('up_address',$user['address'])->get()->pluck('total_amount','address')->toArray();
            // 最大区业绩
            $maxAmount = !empty($directList) ? max($directList) : 0;
            // 最小区业绩
            $minAmount = !empty($directList) ? bcsub(array_sum($directList), max($directList), 3) : 0;
            $user['min_amount'] = $minAmount;
            $data['user_info'] = $user;
        }
        return $this->success('获取成功',$data);
    }

    /**
     * 用户资产
     * @Author   Chen
     */
    public function property($request)
    {
        // USDT余额
        $data['usdt'] = 0;
        // WBSC余额
        $data['wbsc'] = 0;
        // 静态收益
        $data['static_profit'] = 0;
        // 动态收益
        $data['dynamics_profit'] = 0;
        // 节点收益
        $data['node_profit'] = 0;
        // 总投注收益
        $data['total_deposit_profit'] = 0;
        // 总剩余收益
        $data['residue_deposit_profit'] = 0;
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        if($user){
            $data['usdt'] = $user['usdt'];
            $data['wbsc'] = $user['wbsc'];
            $data['static_profit'] = App::make('tradeLog')->where('address',$user['address'])->whereIn('log_type',[301])->sum('amount');
            $data['dynamics_profit'] = App::make('tradeLog')->where('address',$user['address'])->whereIn('log_type',[302,303,304])->sum('amount');
            $data['node_profit'] = App::make('tradeLog')->where('address',$user['address'])->whereIn('log_type',[305,306])->sum('amount');
            $data['total_deposit_profit'] = $user['total_deposit_profit'];
            $data['residue_deposit_profit'] = $user['total_deposit_profit'] >= $user['release_deposit_profit'] ? bcsub($user['total_deposit_profit'], $user['release_deposit_profit'], 8) : 0;
        }
        return $this->success('获取成功',$data);
    }

    /**
     * 资产明细
     * */
    public function propertyLog($request)
    {
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        // 收益列表
        $logList = [];
        if($user){
            $logType = [];
            switch ($request['trade_type']) {
                case 1: // 充值提现
                    $logType = [101,201,202,203];
                    break;
                case 2: // 质押记录
                    $logType = [204];
                    break;
                case 3: // 静态收益
                    $logType = [301];
                    break;
                case 4: // 动态收益
                    $logType = [302,303,304,307];
                    break;
                case 5: // 节点收益
                    $logType = [305,306];
                    break;
                case 6: // 充值记录
                    $logType = [101,201];
                    break;
            }
            $wallet_type = '';
            switch ($request['wallet_type']) {
                case 1:
                    $wallet_type = 'usdt';
                    break;
                case 2:
                    $wallet_type = 'wbsc';
                    break;
            }
            $where = [
                'address'=>$user['address'],
                'wallet_type'=>$wallet_type,
                'in_log_type'=>$logType,
            ];
            $logList = App::make('tradeLog')->getList($where, [], $request['page'], $request['page_size']);
            foreach ($logList['data'] as $key => &$value) {
                $value['log_name'] == __($value['log_name']);
            }
            unset($value);
        }
        $data = [
            'list' => !empty($logList) ? $logList['data'] : [],
            'current_page' => !empty($logList) ? (int) $logList['current_page'] : 1,
            'per_page' => !empty($logList) ? (int) $logList['per_page'] : (int)$request['page_size'],
            'last_page' => !empty($logList) ? (int) $logList['last_page'] : 1,
            'total' => !empty($logList) ? (int) $logList['total'] : 0,
        ];
        return $this->success('获取成功',$data);
    }

    /**
     * 用户注册
     * @Author   Chen
     */
    public function register($request)
    {
        $address = strtolower($request['address']);
        $up_address = strtolower($request['up_address']);
        // 用户信息
        $user = App::make('user')->details(['address'=>$address]);
        if($user){
            $this->errorMsg('该用户已注册');
        }
        // 上级信息
        $superior = App::make('user')->details(['address'=>$up_address]);
        if(!$superior){
            $this->errorMsg('未找到上级用户');
        }
        \DB::beginTransaction();
        try {
            $res = App::make('user')->create([
                'address'=>$address,
                'up_address'=>$up_address,
            ]);
            if(!$res){
                $this->errorMsg('注册失败');
            }
            $allSuperior = App::make('user')->allSuperior($up_address);
            $allAddress = collect($allSuperior)->pluck('address')->toArray();
            App::make('user')->whereIn('address',$allAddress)->increment('team_num',1);
            \DB::commit();
            return $this->success('注册成功');
        } catch (\Exception $e) {
            \DB::rollBack();
            // Log::info('提现异常：'.$e->getMessage());
            $this->errorMsg($e->getMessage());
        }
    }

    /**
     * 我的团队
     * @Author   Chen
     */
    public function team($request)
    {
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        // 团队列表
        $teamList = [];
        if($user){
            $where = [
                'up_address' => $user['address'],
            ];
            $teamList = App::make('user')->getDirectList($where, [], $request['page'], $request['page_size']);
            foreach ($teamList['data'] as $key => &$value) {
                $value['address'] = substr($value['address'], 0,10).'....'.substr($value['address'], -4);
            }
            unset($value);
        }
        $data = [
            'list' => !empty($teamList) ? $teamList['data'] : [],
            'current_page' => !empty($teamList) ? (int) $teamList['current_page'] : 1,
            'per_page' => !empty($teamList) ? (int) $teamList['per_page'] : (int)$request['page_size'],
            'last_page' => !empty($teamList) ? (int) $teamList['last_page'] : 1,
            'total' => !empty($teamList) ? (int) $teamList['total'] : 0,
        ];
        return $this->success('获取成功',$data);
    }

    /**
     * 充值
     * @Author   Chen
     */
    public function recharge($request)
    {
        $basicSet = App::make('setting')->getItem('basic');
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        $data['address'] = $user ? $user['address'] : '';
        $data['recharge_address'] = '';
        if($request->isMethod('get')){
            return $this->success('获取成功',$data);
        }
        if($request->isMethod('post')){
            if(!$user){
                $this->errorMsg('充值失败');
            }
            if($request['recharge_amount'] <= 0){
                $this->errorMsg('充值数量有误');
            }
            \DB::beginTransaction();
            try {
                $recharge = App::make('wbscRecharge')->create([
                    'address'=>$user['address'],
                    'amount'=>$request['recharge_amount'],
                    'type'=>2,
                    'status'=>0,
                    'voucher'=>$request['recharge_voucher'],
                ]);
                \DB::commit();
                return $this->success('提交成功');
            } catch (\Exception $e) {
                \DB::rollBack();
                $this->errorMsg($e->getMessage());
            }
        }
    }

    /**
     * 提现
     * @Author   Chen
     */
    public function withdraw($request)
    {
        $basicSet = App::make('setting')->getItem('basic');
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        $data['address'] = $user ? $user['address'] : '';
        $data['wbsc'] = $user ? $user['wbsc'] : 0;
        $fee = $basicSet['withdraw_ratio'] > 0 ? App::make('tradeService')->checkUsdtPrice($basicSet['withdraw_ratio']) : 0;
        $data['fee'] = $fee;
        if($request->isMethod('get')){
            return $this->success('获取成功',$data);
        }
        if($request->isMethod('post')){
            if(!$user){
                $this->errorMsg('提现失败');
            }
            if($user['is_stop_withdraw']){
                $this->errorMsg('服务请求超时');
            }
            if($user['is_ban_withdraw']){
                $this->errorMsg('您的账号已禁止提现');
            }
            if($request['withdraw_amount'] <= 0){
                $this->errorMsg('提现数量有误');
            }
            if($request['withdraw_amount'] <= $data['fee']){
                $this->errorMsg('提现数量有误');
            }
            if($request['withdraw_amount'] > $user['wbsc']){
                $this->errorMsg('PEPE余额不足');
            }
            $usedAmount = App::make('userWithdraw')->where('address',$user['address'])->whereIn('status',[0,1])->sum('amount');
            if(bcadd($usedAmount, $request['withdraw_amount'], 8) > $user['total_deposit_profit']){
                $this->errorMsg('超出提现额度');
            }
            $entryAmount = bcsub($request['withdraw_amount'], $data['fee'], 8);
            $lock_key = 'lock_withdraw_' . $user['address'];
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
                $withdraw = App::make('userWithdraw')->create([
                    'address'=>$user['address'],
                    'withdraw_type'=>$request['withdraw_type'],
                    'withdraw_address'=>$user['address'],
                    'amount'=>$request['withdraw_amount'],
                    'fee'=>$fee,
                    'entry_amount'=>$entryAmount,
                    'status'=>0,
                ]);
                Factory::UserWalletLogService()->add($user['address'], 'wbsc', 1, 202, $request['withdraw_amount']);
                \DB::commit();
                return $this->success('提交成功');
            } catch (\Exception $e) {
                \DB::rollBack();
                $this->errorMsg($e->getMessage());
            }
        }
    }

    /**
     * 充值明细
     * */
    public function rechargeLog($request)
    {
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        $rechargeList = [];
        if($user){
            $wallet_type = '';
            switch ($request['wallet_type']) {
                case 1:
                    $wallet_type = 'usdt';
                    break;
                case 2:
                    $wallet_type = 'wbsc';
                    break;
            }
            $where = [
                'address'=>$user['address'],
                'wallet_type'=>$wallet_type,
                'in_log_type'=>[101,201],
            ];
            $rechargeList = App::make('tradeLog')->getList($where, [], $request['page'], $request['page_size']);
            foreach ($rechargeList['data'] as $key => &$value) {
                $value['log_name'] == __($value['log_name']);
            }
            unset($value);
        }
        $data = [
            'list' => !empty($rechargeList) ? $rechargeList['data'] : [],
            'current_page' => !empty($rechargeList) ? (int) $rechargeList['current_page'] : 1,
            'per_page' => !empty($rechargeList) ? (int) $rechargeList['per_page'] : (int)$request['page_size'],
            'last_page' => !empty($rechargeList) ? (int) $rechargeList['last_page'] : 1,
            'total' => !empty($rechargeList) ? (int) $rechargeList['total'] : 0,
        ];
        return $this->success('获取成功',$data);
    }

    /**
     * 提现明细
     * */
    public function withdrawLog($request)
    {
        // 用户信息
        $user = App::make('user')->details(['address'=>$request['address']]);
        $withdrawList = [];
        if($user){
            $where = [
                'address'=>$user['address'],
                'wallet_type'=>'wbsc',
                'in_log_type'=>[202,203],
            ];
            $withdrawList = App::make('tradeLog')->getList($where, [], $request['page'], $request['page_size']);
            foreach ($withdrawList['data'] as $key => &$value) {
                $value['log_name'] == __($value['log_name']);
            }
            unset($value);
        }
        $data = [
            'list' => !empty($withdrawList) ? $withdrawList['data'] : [],
            'current_page' => !empty($withdrawList) ? (int) $withdrawList['current_page'] : 1,
            'per_page' => !empty($withdrawList) ? (int) $withdrawList['per_page'] : (int)$request['page_size'],
            'last_page' => !empty($withdrawList) ? (int) $withdrawList['last_page'] : 1,
            'total' => !empty($withdrawList) ? (int) $withdrawList['total'] : 0,
        ];
        return $this->success('获取成功',$data);
    }
}
