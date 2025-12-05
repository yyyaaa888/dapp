<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGrade extends Model
{
    // 用户等级
    protected $table = 'user_grade';

    // 表主键
    protected $primaryKey = 'grade_id';

    protected $hidden = ['created_at','updated_at'];

    // public function getUpgradeAttribute($value)
    // {
    //     return $value ? json_decode($value,true) : [];
    // }

    // public function setUpgradeAttribute($value)
    // {
    //     $this->attributes['upgrade'] = json_encode($value);
    // }

    public function getEquityAttribute($value)
    {
        return $value ? json_decode($value,true) : [];
    }

    public function setEquityAttribute($value)
    {
        $this->attributes['equity'] = json_encode($value);
    }

    //列表
    public function getListAll($sort = 'asc')
    {
        return $this->query()->where('status',1)->orderBy('weight',$sort)->get();
    }

    //列表
    public function getUpList($sort = 'asc')
    {
        return $this->query()->where('status',1)->where('grade_id',1)->orderBy('weight',$sort)->get();
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['grade_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }
}
