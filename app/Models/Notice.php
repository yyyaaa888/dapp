<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{

    protected $table = 'notice';

    // 表主键
    protected $primaryKey = 'notice_id';

    protected $hidden = ['updated_at'];
    
    //追加到模型数组表单的访问器
    protected $appends = ['year','month'];

    public function getYearAttribute()
    {
        if(isset($this->attributes['created_at'])){
            return date('Y',strtotime($this->attributes['created_at']));
        }
    }

    public function getMonthAttribute()
    {
        if(isset($this->attributes['created_at'])){
            return date('m-d',strtotime($this->attributes['created_at']));
        }
    }

    //列表
    public function getList($where, $with = [], $page = 1, $page_size = 15)
    {
        $model = $this->query();
        $model = $model->with($with);
        $model = $model->orderBy('created_at','desc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }

    //列表
    public function getListAll()
    {
        return $this->query()->orderBy('created_at','desc')->get();
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['notice_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }
}
