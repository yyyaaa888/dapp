<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\DB;

class Prize extends Model
{
	// 开奖
    protected $table = 'prize';

    // 表主键
    protected $primaryKey = 'prize_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','amount'];

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['prize_id'] = (int)$where;
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
