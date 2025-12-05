<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class FreezeProfit extends Model
{
	// 冻结收益
    protected $table = 'freeze_profit';

    // 表主键
    protected $primaryKey = 'profit_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','up_address','log_type','profit_amount','residue_amount','remarks','is_settle'];

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class,'address','address');
    }

    //列表
    public function getList($where, $with = [], $page = 1, $page_size = 15)
    {
        $model = $this->query();
        $model = $model->with($with);
        if(isset($where['address']) && !empty($where['address'])){
            $model = $model->where('address',$where['address']);
        }
        if(isset($where['up_address']) && !empty($where['up_address'])){
            $model = $model->where('up_address',$where['up_address']);
        }
        $model = $model->orderBy('created_at','asc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['profit_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
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
