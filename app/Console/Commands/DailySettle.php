<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DailySettle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailySettle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '全网分红';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \DB::beginTransaction();
        try {
            // 发放全网分红
            $this->dividend();
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::info('全网分红异常：'.$e->getMessage());
        }
    }

    // 发放全网分红
    public function dividend()
    {
        $nodeArr = [
            '1'=>20,
            '2'=>30,
        ];
        $log = App::make('tradeLog')->where(['wallet_type'=>'wbsc','log_type'=>305])->whereDate('created_at',date('Y-m-d'))->count();
        if($log <= 0){
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
                                Factory::UserWalletLogService()->add($user['address'], 'wbsc', 0, 305, $aveAmont);
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
    }
}
