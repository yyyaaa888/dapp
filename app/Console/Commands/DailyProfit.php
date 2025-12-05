<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DailyProfit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DailyProfit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '静态收益';

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
        $basicSet = App::make('setting')->getItem('basic');
        \DB::beginTransaction();
        try {
            $log = App::make('tradeLog')->where(['wallet_type'=>'wbsc','log_type'=>301])->whereDate('created_at',date('Y-m-d'))->count();
            if($log <= 0){
            // if(1){
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
                    if($basicSet['direct_ratio'] > 0){
                        $directAmount = bcdiv(bcmul($deposit['amount'], ($basicSet['direct_ratio'] / 100), 6), 200, 6);
                        if($directAmount > 0 && $deposit->profit_days < 200){
                            $directUser = $deposit['user'] && $deposit['user']['up_address'] && !$deposit['user']['is_stop_profit'] ? App::make('user')->details(['address'=>$deposit['user']['up_address']]) : [];
                            if(!empty($directUser) && $directUser['direct_num'] >= 10){
                                // 直推收益
                                Factory::UserWalletLogService()->add($directUser['address'], 'wbsc', 0, 302, $directAmount,'来源地址：'.$deposit['user']['address']);
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
    }
}
