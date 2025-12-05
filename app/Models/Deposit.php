<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\DB;

class Deposit extends Model
{
	// 入金
    protected $table = 'deposit';

    // 表主键
    protected $primaryKey = 'deposit_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','deposit_type','amount','wbsc_price','usdt_amount','profit_amount','release_amount','total_direct_profit','grant_direct_profit','direct_days','direct_profit','is_direct_num'];

    public function getAmountAttribute()
    {
        if(isset($this->attributes['amount'])){
            return sctonum($this->attributes['amount']);
        }
    }

    public function getUsdtAmountAttribute()
    {
        if(isset($this->attributes['usdt_amount'])){
            return sctonum($this->attributes['usdt_amount']);
        }
    }

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class,'address','address');
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['deposit_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    // 结算
    public function settle()
    {
        $basicSet = App::make('setting')->getItem('basic');
        $user = App::make('user')->details(['address'=>$this->address]);
        // 所有上级
        $allSuperior = $user && $user['up_address'] ? App::make('user')->allSuperior($user['up_address']) : [];
        if($user && $user['up_address']){
            $direct_num = App::make('user')->where(['up_address'=>$user['up_address'],'is_deposit'=>1])->count();
            if($direct_num > 0){
                App::make('user')->where('address',$user['up_address'])->update(['direct_num'=>$direct_num]);
            }
        }
        // 上级升级
        $this->superiorUpGrade($allSuperior,$this->usdt_amount);
        // 发放节点奖励
        $this->nodeReward($basicSet,$allSuperior,$this->amount);
    }

    // 上级升级
    public function superiorUpGrade($allSuperior,$amount){
        if(!empty($allSuperior)){
            // 增加所有上级团队业绩
            $allUpAddress = collect($allSuperior)->pluck('address')->toArray();
            App::make('user')->whereIn('address',$allUpAddress)->increment('team_amount',$amount);

            // 所有上级
            $allSuperior = App::make('user')->whereIn('address',$allUpAddress)->get();
            // 所有上级的直推
            $allSuperiorDirect = App::make('user')->select([
                   'address','up_address','personal_amount','team_amount',DB::raw('sum(personal_amount + team_amount) as  total_amount')
                ])
                ->whereIn('up_address',$allUpAddress)
                ->groupBy('address')
                ->get()
                ->toArray();
            foreach ($allSuperior as $key => $superior) {
                $directList = collect($allSuperiorDirect)->whereIn('up_address',$superior['address'])->pluck('total_amount','address')->toArray();
                // 最大区业绩
                $maxAmount = !empty($directList) ? max($directList) : 0;
                // 最小区业绩
                $minAmount = !empty($directList) ? bcsub(array_sum($directList), max($directList), 3) : 0;
                $grade = 0;
                if($minAmount >= 100000){
                    $grade = 4;
                }elseif($minAmount >= 50000){
                    $grade = 3;
                }elseif($minAmount >= 20000){
                    $grade = 2;
                }elseif($minAmount >= 5000){
                    $grade = 1;
                }
                if($grade > 0 && $grade > $superior['grade_id']){
                    App::make('user')->where('address',$superior['address'])->update(['grade_id'=>$grade]);
                }
            }
        }
    }

    // 发放节点奖励
    public function nodeReward($basicSet,$allSuperior,$amount)
    {
        if(!empty($allSuperior)){
            $nodeTeam = [];
            foreach ($allSuperior as $key => $superior) {
                if($superior['is_node'] > 0){
                    if(empty($nodeTeam)){
                        $nodeRatio = $superior['is_node'] == 1 ? $basicSet['min_node_share_ratio'] : $basicSet['big_node_share_ratio'];
                        $nodeAmount = $nodeRatio > 0 ? bcmul($amount, ($nodeRatio / 100), 8) : 0;
                        $nodeLevel = $superior['is_node'];
                        $nodeTeam[] = [
                            'address'=>$superior['address'],
                            'ratio'=>$nodeRatio,
                            'amount'=>$nodeAmount,
                            'level'=>$nodeLevel,
                        ];
                    }else{
                        $lastNode = $nodeTeam[count($nodeTeam) - 1];
                        if($lastNode['level'] == 1 && $superior['is_node'] == 2){
                            $nodeRatio = bcsub($basicSet['big_node_share_ratio'], $lastNode['ratio'], 4);
                            $nodeAmount = $nodeRatio > 0 ? bcmul($amount, ($nodeRatio / 100), 8) : 0;
                            $nodeLevel = 2;
                            $nodeTeam[] = [
                                'address'=>$superior['address'],
                                'ratio'=>$nodeRatio,
                                'amount'=>$nodeAmount,
                                'level'=>$nodeLevel,
                            ];
                        }
                    }
                }
            }
            if(!empty($nodeTeam)){
                foreach ($nodeTeam as $key => $node) {
                    $user = App::make('user')->details(['address'=>$node['address']]);
                    if($user){
                        // 剩余收益
                        // $residueProfit = bcsub($user['total_deposit_profit'], $user['release_deposit_profit'], 8);
                        // $isProfitEnd = 0;
                        // if($node['amount'] >= $residueProfit){
                        //     $profitAmount = $residueProfit;
                        //     $isProfitEnd = 1;
                        // }else{
                        //     $profitAmount = $node['amount'];
                        // }
                        if($node['amount'] > 0 && !$user['is_stop_profit']){
                            Factory::UserWalletLogService()->add($node['address'], 'pepe', 0, 306, $node['amount']);
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

    // 全局作用域
    public static function boot()
    {
        parent::boot();

        // 处理 [ 新增 ] 事件
        static::created(function ($model)
        {
            
        });

        // 处理 [ 删除 ] 事件
        static::deleted(function ($model)
        {
            
        });

        // 处理 [ 更新 ] 事件
        static::updated(function ($model)
        {
            
        });
    }
}
