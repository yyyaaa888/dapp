<?php

namespace App\Service\Api;

use App\Service\BaseService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Web3p\EthereumTx\Transaction;
use Web3p\RLP\RLP;
use Web3\Web3;
use Web3\Contract;
use App\Service\Eth\Callback;

class SystemService extends BaseService
{
    /**
     * 更新价格
     * @Author   Chen
     */
    public function updatePrice($request)
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
            $this->errorMsg($e->getMessage());
        }
        $pepe_price = Redis::exists($priceKey) ? Redis::get($priceKey) : 0;
        return $this->success('获取成功',['pepe_price'=>$pepe_price]);
    }

    /**
     * 全网分红
     * @Author   Chen
     */
    public function dividend()
    {
        die;
        \DB::beginTransaction();
        try {
            $nodeArr = [
                '1'=>20,
                '2'=>30,
            ];
            $log = App::make('tradeLog')->where(['wallet_type'=>'pepe','log_type'=>305])->whereDate('created_at',date('Y-m-d'))->count();
            // if($log <= 0){
            if(1){
                // 总手续费
                $totalFeeAmount = App::make('userWithdraw')->whereDate('audit_time',date('Y-m-d', strtotime('-1 day')))->where('status',1)->sum('fee');
                if($totalFeeAmount > 0){
                    foreach($nodeArr as $node => $nodeRatio){
                        $totalAmount = $totalFeeAmount > 0 ? bcmul($totalFeeAmount, ($nodeRatio / 100), 8) : 0 ;
                        $userList = App::make('user')->select('address','total_deposit_profit','release_deposit_profit','is_node','is_stop_profit')->where('is_node',$node)->get();
                        $totalNum = count($userList);
                        $aveAmont = $totalNum > 0 ? bcdiv($totalAmount, $totalNum, 8) : 0;
                        foreach ($userList as $key => $user) {
                            // 剩余收益
                            $residueProfit = bcsub($user['total_deposit_profit'], $user['release_deposit_profit'], 8);
                            $isProfitEnd = 0;
                            if(!$user['is_stop_profit']){
                                if($aveAmont >= $residueProfit){
                                    $profitAmount = $residueProfit;
                                    $isProfitEnd = 1;
                                }else{
                                    $profitAmount = $aveAmont;
                                }
                                if($aveAmont > 0){
                                    Factory::UserWalletLogService()->add($user['address'], 'pepe', 0, 305, $aveAmont);
                                }
                            }
                            // if($isProfitEnd){
                            //     $user->is_profit_end = $isProfitEnd;
                            //     $user->save();
                            // }
                        }
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            $this->errorMsg($e->getMessage());
        }
        return $this->success('发放成功');
    }

    /**
     * 静态收益
     * @Author   Chen
     */
    public function staticProfit()
    {
        $basicSet = App::make('setting')->getItem('basic');
        \DB::beginTransaction();
        try {
            $log = App::make('tradeLog')->where(['wallet_type'=>'wbsc','log_type'=>301])->whereDate('created_at',date('Y-m-d'))->count();
            // if($log <= 0){
            if(1){
                $staticRatio = $basicSet['static_ratio'];
                $list = App::make('deposit')->with(['user:user_id,address,up_address'])->where('status',0)->get();
                foreach ($list as $deposit){
                    if($basicSet['static_ratio'] > 0){
                        $staticAmount = bcdiv(bcmul($deposit['amount'], (1 + ($basicSet['static_ratio'] / 100)), 6), 400, 6);
                        if($staticAmount > 0 && $deposit->profit_days < 400 && $deposit['user'] && !$deposit['user']['is_stop_profit']){
                            $deposit->release_amount += $staticAmount;
                            $deposit->profit_days += 1;
                            if($deposit->profit_days == 400){
                                $deposit->status = 1;
                            }
                            $deposit->save();
                            // 静态收益
                            Factory::UserWalletLogService()->add($deposit['address'], 'wbsc', 0, 301, $staticAmount);
                        }
                    }
                    $directUser = $deposit['user'] && $deposit['user']['up_address'] && !$deposit['user']['is_stop_profit'] ? App::make('user')->details(['address'=>$deposit['user']['up_address']]) : [];
                    if(!empty($directUser) && $deposit['direct_profit'] > 0){
                        if(!$deposit['is_direct_num']){
                            if($directUser['direct_num'] >= 10){
                                $residueAmount = bcsub($deposit['total_direct_profit'], $deposit['grant_direct_profit'], 6);
                                $directProfit = bcdiv($residueAmount, 200, 6);
                                $deposit->is_direct_num = 1;
                                $deposit->direct_profit = $directProfit;
                                $deposit->grant_direct_profit += $directProfit;
                                $deposit->direct_days = 1;
                                $deposit->save();
                                // 直推收益
                                Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directProfit,'来源地址：'.$deposit['user']['address']);
                            }else{
                                if($deposit->direct_days < 400){
                                    if($directUser['direct_num'] >= 10){
                                        $residueAmount = bcsub($deposit['total_direct_profit'], $deposit['grant_direct_profit'], 6);
                                        $directProfit = bcdiv($residueAmount, 200, 6);
                                        $deposit->is_direct_num = 1;
                                        $deposit->direct_profit = $directProfit;
                                        $deposit->grant_direct_profit += $directProfit;
                                        $deposit->direct_days = 1;
                                        $deposit->save();
                                        // 直推收益
                                        Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directProfit,'来源地址：'.$deposit['user']['address']);
                                    }else{
                                        $directProfit = $deposit['direct_profit'];
                                        $residueDirectProfit = bcsub($deposit->total_direct_profit, $deposit->grant_direct_profit, 6);
                                        if($residueDirectProfit <= $deposit['direct_profit']){
                                            $directProfit = $residueDirectProfit;
                                        }
                                        if($directProfit > 0){
                                            $deposit->grant_direct_profit += $directProfit;
                                            $deposit->direct_days += 1;
                                            $deposit->save();
                                            // 直推收益
                                            Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directProfit,'来源地址：'.$deposit['user']['address']);
                                        }
                                    }
                                }
                            }
                        }else{
                            if($deposit->direct_days < 200){
                                $directProfit = $deposit['direct_profit'];
                                $residueDirectProfit = bcsub($deposit->total_direct_profit, $deposit->grant_direct_profit, 6);
                                if($residueDirectProfit <= $deposit['direct_profit']){
                                    $directProfit = $residueDirectProfit;
                                }
                                if($directProfit > 0){
                                    $deposit->grant_direct_profit += $directProfit;
                                    $deposit->direct_days += 1;
                                    $deposit->save();
                                    // 直推收益
                                    Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directProfit,'来源地址：'.$deposit['user']['address']);
                                }
                            }
                        }
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::info('静态收益异常：'.$e->getMessage());
        }
        dd('发放成功');


        $basicSet = App::make('setting')->getItem('basic');
        \DB::beginTransaction();
        try {
            $log = App::make('tradeLog')->where(['wallet_type'=>'wbsc','log_type'=>301])->whereDate('created_at',date('Y-m-d'))->count();
            // if($log <= 0){
            if(1){
                $startDate = date("Y-m-d", strtotime("-4 day"));
                dump($startDate);
                $list = App::make('deposit')->with(['user:user_id,address,up_address'])->whereDate('created_at','<',$startDate)->where('status',0)->whereIn('address',['0x161366523406ba8e8075012af981f866f515528b','0xb329f2bf35ef08011e0f1ad2137293a88ce5110b'])->get();
                // dd($list->toArray());
                foreach ($list as $deposit){
                    $endDays = (strtotime(date('Y-m-d')) - strtotime(date('Y-m-d',strtotime($deposit['created_at'])))) / 86400;
                    if($deposit->profit_days < $endDays){
                    if($basicSet['static_ratio'] > 0){
                        dump($endDays);
                        // dd($deposit->toArray());
                            $staticAmount = bcdiv(bcmul($deposit['amount'], (1 + ($basicSet['static_ratio'] / 100)), 6), 400, 6);
                            if($staticAmount > 0 && $deposit->profit_days < 400 && $deposit['user'] && !$deposit['user']['is_stop_profit']){
                                $deposit->release_amount += $staticAmount;
                                $deposit->profit_days += 1;
                                if($deposit->profit_days == 400){
                                    $deposit->status = 1;
                                }
                                $deposit->save();
                                dump('发放成功');
                                // 静态收益
                                // Factory::UserWalletLogService()->add($deposit['address'], 'wbsc', 0, 301, $staticAmount);
                            }
                    }
                    if($basicSet['direct_ratio'] > 0){
                        $directAmount = bcdiv(bcmul($deposit['amount'], ($basicSet['direct_ratio'] / 100), 6), 200, 6);
                        if($directAmount > 0 && $deposit->profit_days < 200){
                            $directUser = $deposit['user'] && $deposit['user']['up_address'] && !$deposit['user']['is_stop_profit'] ? App::make('user')->details(['address'=>$deposit['user']['up_address']]) : [];
                            if(!empty($directUser) && $directUser['direct_num'] >= 10){
                                // 直推收益
                                // Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directAmount,'来源地址：'.$deposit['user']['address']);
                            }
                        }
                    }
                    }
                }
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            dd('静态收益异常：'.$e->getMessage());
        }
        dd(222);
        return $this->success('发放成功');
    }

    /**
     * 公告列表
     * @Author   Chen
     */
    public function noticeList($request)
    {
        // 公告列表
        $noticeList = App::make('notice')->getList([], [], $request['page'], $request['page_size']);
        foreach($noticeList['data'] as &$value){
            $value['title'] = $request->header("lang") == 'cn' ? $value['cn_title'] : $value['en_title'];
            $value['content'] = $request->header("lang") == 'cn' ? $value['cn_content'] : $value['en_content'];
            unset($value['cn_title']);
            unset($value['en_title']);
            unset($value['cn_content']);
            unset($value['en_content']);
        }
        unset($value);
        $data = [
            'list' => $noticeList['data'],
            'current_page' => (int)$noticeList['current_page'],
            'per_page' => (int)$noticeList['per_page'],
            'last_page' => (int)$noticeList['last_page'],
            'total' => (int)$noticeList['total'],
        ];
        return $this->success('获取成功',$data);
    }

    /**
     * 公告详情
     * @Author   Chen
     */
    public function noticeDetail($request)
    {
        // 公告详情
        $notice = App::make('notice')->details($request['notice_id']);
        $this->checkIsNull($notice, '未找到公告');
        $notice = $notice->toArray();
        $notice['title'] = $request->header("lang") == 'cn' ? $notice['cn_title'] : $notice['en_title'];
        $notice['content'] = $request->header("lang") == 'cn' ? $notice['cn_content'] : $notice['en_content'];
        unset($notice['cn_title']);
        unset($notice['en_title']);
        unset($notice['cn_content']);
        unset($notice['en_content']);
        return $this->success('获取成功',$notice);
    }

    /**
     * 帮助中心
     * @Author   Chen
     */
    public function help()
    {
        $helpSet = App::make('setting')->getItem('help');
        return $this->success('获取成功',$helpSet);
    }

    /**
     * 联系客服
     * @Author   Chen
     */
    public function service()
    {
        $serviceSet = App::make('setting')->getItem('service');
        return $this->success('获取成功',$serviceSet);
    }


    /**
     * 文件上传
     * @Author   Chen
     */
    public function uploadFile($request)
    {
        // 获取文件信息
        $file = $request->file('file');
        if(empty($file)){
            $this->errorMsg('请选择上传文件');
        }
        // 文件大小
        $fileSize = $file->getClientSize();
        if($fileSize / 1024 / 1024 > 20){
            $this->errorMsg('上传文件不能大于20M');
        }
        // 文件类型
        $mimeType = explode('/',$file->getMimeType());
        if(!in_array($mimeType[0], ['image'])){
            $this->errorMsg('上传的文件类型有误');
        }
        switch ($request->input('type')) {
            case 1:
                $path = 'api/images/' . date('Ymd');
                break;
            // case 2:
            //     $path = 'api/files/' . date('Ymd');
            //     break;
            default:
                $this->errorMsg('无效上传类型');
                break;
        }
        $res = Storage::disk('local')->put($path,$file);
        return $this->success('上传成功',['path' => env('APP_URL') .'/uploads/'. $res]);
    }

    public function task()
    {
        die;
        \DB::beginTransaction();
        try {
            $list = App::make('userWithdraw')->where('status',0)->whereNull('trade_hash')->limit(1)->get();
            foreach ($list as $key => $withdraw) {
                $abi = '[{"inputs":[{"internalType":"address","name":"initialOwner","type":"address"}],"stateMutability":"nonpayable","type":"constructor"},{"inputs":[{"internalType":"address","name":"target","type":"address"}],"name":"AddressEmptyCode","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"AddressInsufficientBalance","type":"error"},{"inputs":[],"name":"FailedInnerCall","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"InvestInvalid","type":"error"},{"inputs":[{"internalType":"address","name":"owner","type":"address"}],"name":"OwnableInvalidOwner","type":"error"},{"inputs":[{"internalType":"address","name":"account","type":"address"}],"name":"OwnableUnauthorizedAccount","type":"error"},{"inputs":[{"internalType":"address","name":"user","type":"address"},{"internalType":"address","name":"recommended","type":"address"}],"name":"RegisterInvalid","type":"error"},{"inputs":[{"internalType":"address","name":"token","type":"address"}],"name":"SafeERC20FailedOperation","type":"error"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakwAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"BuyTicket","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":false,"internalType":"uint256","name":"cakwAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"cakeAmount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Invest","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousOwner","type":"address"},{"indexed":true,"internalType":"address","name":"newOwner","type":"address"}],"name":"OwnershipTransferred","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"account","type":"address"},{"indexed":true,"internalType":"address","name":"referRecommender","type":"address"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Register","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousCakwAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newCakwAddr","type":"address"}],"name":"SetCakwAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousTicketAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newTicketAddr","type":"address"}],"name":"SetTicketAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"previousWithdrawAddr","type":"address"},{"indexed":true,"internalType":"address","name":"newWithdrawAddr","type":"address"}],"name":"SetWithdrawAddr","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"internalType":"address","name":"user","type":"address"},{"indexed":false,"internalType":"uint256","name":"amount","type":"uint256"},{"indexed":false,"internalType":"uint256","name":"time","type":"uint256"}],"name":"Withdraw","type":"event"},{"inputs":[],"name":"BASE_SCALE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEPOOL","outputs":[{"internalType":"contractICakePool","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKEToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKWToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKW_APPRECIATION_BASE_QUANTITY","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"CAKW_BASE_PRICE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"DESTRUCTION_SCALE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"MIN_INVEST_CAKE","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"USDTToken","outputs":[{"internalType":"contractIERC20","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"burnCAKW","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"name":"buyCakwByCalcCakeAmount","outputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"name":"buyTicket","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"cakwAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKEPOOLData","outputs":[{"internalType":"int128","name":"amount","type":"int128"},{"internalType":"uint256","name":"end","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKEPrice","outputs":[{"internalType":"uint256","name":"amountOut","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"getCAKWPrice","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"invest","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"uint256","name":"cakeAmount","type":"uint256"}],"name":"investCakeByBurnCAKWAmount","outputs":[{"internalType":"uint256","name":"cakwAmount","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"isInvest","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"view","type":"function"},{"inputs":[],"name":"owner","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"","type":"address"}],"name":"recommended","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"recommendedAddr","type":"address"}],"name":"register","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"renounceOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"sellCAKW","outputs":[{"internalType":"uint256","name":"","type":"uint256"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setCakwAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setTicketAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"}],"name":"setWithdrawAddr","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"ticketAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"},{"inputs":[{"internalType":"address","name":"newOwner","type":"address"}],"name":"transferOwnership","outputs":[],"stateMutability":"nonpayable","type":"function"},{"inputs":[{"internalType":"address","name":"addr","type":"address"},{"internalType":"uint256","name":"amount","type":"uint256"}],"name":"withdraw","outputs":[{"internalType":"bool","name":"","type":"bool"}],"stateMutability":"nonpayable","type":"function"},{"inputs":[],"name":"withdrawAddr","outputs":[{"internalType":"address","name":"","type":"address"}],"stateMutability":"view","type":"function"}]';
                $web3Url = env('PRC_URL');
                $contract = new Contract($web3Url, $abi);
                $withdrawKey = env('APP_NAME').'_withdraw_number';
                $this->chainId = 56;
                $this->withdrawAddress = env('FORMAL_ADDRESS');
                $this->privateKey = env('WITHDRAW_PRIVATE_KEY');
                $this->walletAddress = env('WITHDRAW_ADDRESS');
                Redis::set($withdrawKey,12);
                // dd(Redis::get($withdrawKey));
                $nonce = Redis::exists($withdrawKey) ? Redis::get($withdrawKey) : 7;
                $method = 'withdraw';
                $to_addr = $withdraw['address'];
                $number = bcmul($withdraw['amount'],bcpow(10,18));
                $data = '0x' . $contract->at($this->withdrawAddress)->getData($method, $to_addr, $number);
                $contract_transfer_gas = getEthGas();
                $apiUrl = env('API_URL');
                $gas_price = getEthGasPrice($apiUrl);
                $web3 = new Web3($web3Url);
                $eth = $web3->eth;

                $txParams = [
                    'from' => $this->walletAddress,
                    'to' => $this->withdrawAddress,
                    'value' => '0x0',//合约交易固定0,用户之间钱包互转才有对应的值
                    'gas' => $contract_transfer_gas,
                    'gasPrice' => $gas_price,
                    'data' => $data,
                    'chainId' => $this->chainId,
                    'nonce' => '0x'.dechex($nonce),
                ];
                $transaction = new Transaction($txParams);
                $signedTransaction = $transaction->sign($this->privateKey);
                $result = $this->proxyWithdraw($signedTransaction, $apiUrl);
                if(isset($result['error']) && isset($result['error']['message'])){
                    $this->errorMsg($result['error']['message']);
                }
                Redis::incrby($withdrawKey,1);
                $tradeHash = $result['result'];
                $withdraw->trade_hash = $tradeHash;
                $withdraw->save();
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::info('提现异常：'.$e->getMessage());
            $this->errorMsg($e->getMessage());
        }
    }

    public  function proxyWithdraw($hex,$url){
        $param = [
            'module'=>'proxy',
            'action'=>'eth_sendRawTransaction',
            'hex'=>$hex,
            'apikey' => 'W7Q141BZ6BDEJW9X59FH1BJS1UJB44I1N4'
        ];
        
        // $url = $url.'?'.http_build_query($param);
        // dd($url );
        $respon = json_decode(curlGet($url,$param),true);
        return $respon;
    }

    public function bscNotice($request){
        Log::info(var_export($request->all(), true));
    }
}
