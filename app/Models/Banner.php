<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class Banner extends Model
{
    // 轮播图
    protected $table = 'banner';

    // 表主键
    protected $primaryKey = 'banner_id';

    protected $hidden = ['updated_at','created_at'];

    //列表
    public function getListAll()
    {
        return $this->query()->orderBy('sort','asc')->get();
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['banner_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    public static function boot()
    {
        parent::boot();

        //处理 [ 新增 ] 事件
        static::created(function ($model)
        {
            
        });

        //处理 [ 删除 ] 事件
        static::deleted(function ($model)
        {
            
        });

        //处理 [ 更新 ] 事件
        static::updated(function ($model)
        {
            
        });
    }
}
