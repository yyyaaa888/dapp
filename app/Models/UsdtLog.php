<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsdtLog extends Model
{
    // 收益明细
    protected $table = 'usdt_log';

    //表主键
    protected $primaryKey = 'log_id';
    
    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','log_type','cashier_type','amount','balance','remarks'];

    // 属性类型转换
    protected $casts = [
        'amount'    => 'float',
        'balance'    => 'float',
    ];

    //追加到模型数组表单的访问器
    protected $appends = ['log_name'];

    public function getLogNameAttribute()
    {
        if(isset($this->attributes['log_type'])){
            return $this->logType()[$this->attributes['log_type']];
        }
    }

    // 交易类型
    public function logType()
    {
        return [
            '101' => '系统充值',
            '201' => '用户充值',
            '202' => '用户投注',
        ];
    }

    // 收支类型
    public function cashierType()
    {
        return [
            '0' => '收入',
            '1' => '支出',
        ];
    }

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
        if(isset($where['in_log_type']) && !empty($where['in_log_type'])){
            $model = $model->whereIn('log_type',$where['in_log_type']);
        }
        $model = $model->orderBy('created_at','desc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }
}
