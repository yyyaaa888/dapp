<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;

class WbscRecharge extends Model
{
	// WBSC充值
    protected $table = 'wbsc_recharge';

    // 表主键
    protected $primaryKey = 'recharge_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','amount','type','status','voucher','audit_time','trade_hash'];

    //状态
    public function status() {
        return [
            '-1' => '失败',
            '0' => '待审核',
            '1' => '成功',
        ];
    }


    public function getAmountAttribute()
    {
        if(isset($this->attributes['amount'])){
            return sctonum($this->attributes['amount']);
        }
    }

        public function getTotalAmountAttribute()
    {
        if(isset($this->attributes['total_amount'])){
            return sctonum($this->attributes['total_amount']);
        }
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['recharge_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    //列表
    public function getList($where, $with = [], $page = 1, $page_size = 15)
    {
        $model = $this->query();
        $model = $model->with($with);
        if(isset($where['address']) && !empty($where['address'])){
            $model = $model->where('address',$where['address']);
        }
        $model = $model->orderBy('created_at','desc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }

    // 发放节点推荐奖励
    public function grantNodeReward()
    {
        
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
