<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdraw extends Model {

    // 收益提现
    protected $table = 'user_withdraw';

    // 表主键
    protected $primaryKey = 'withdraw_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','withdraw_type', 'withdraw_address', 'amount', 'fee', 'entry_amount','status', 'audit_time'];

    //提现类型
    public function withdrawType() {
        return [
            '0' => '待审核',
            '1' => '提现成功',
        ];
    }

    //提现状态
    public function status() {
        return [
            '-1' => '提现失败',
            '0' => '待审核',
            '1' => '提现成功',
        ];
    }

    public function getAmountAttribute()
    {
        if(isset($this->attributes['amount'])){
            return sctonum($this->attributes['amount']);
        }
    }

    public function getFeeAttribute()
    {
        if(isset($this->attributes['fee'])){
            return sctonum($this->attributes['fee']);
        }
    }

    public function getEntryAmountAttribute()
    {
        if(isset($this->attributes['entry_amount'])){
            return sctonum($this->attributes['entry_amount']);
        }
    }

    //关联用户
    public function user() {
        return $this->hasOne(User::class, 'user_id', 'user_id');
    }

    //列表
    public function getList($where, $with = [], $page = 1, $page_size = 15) {
        $model = $this->query();
        $model = $model->with($with);
        if (isset($where['address']) && !empty($where['address'])) {
            $model = $model->where('address', $where['address']);
        }
        if (isset($where['status']) && in_array($where['status'], ['-1', '0', '1'])) {
            $model = $model->where('status', $where['status']);
        }
        $model = $model->orderBy('created_at', 'desc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }

    //详情
    public function details($where, $with = []) {
        $filter = [];
        if (is_array($where)) {
            $filter = array_merge($filter, $where);
        } else {
            $filter['withdraw_id'] = (int) $where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    // 全局作用域
    public static function boot() {
        parent::boot();

        // 处理 [ 新增 ] 事件
        static::created(function ($model) {

        });

        // 处理 [ 删除 ] 事件
        static::deleted(function ($model) {

        });

        // 处理 [ 更新 ] 事件
        static::updated(function ($model) {

        });
    }
}
