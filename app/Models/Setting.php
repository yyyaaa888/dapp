<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // 系统设置
    protected $table = 'setting';

    // 表主键
    protected $primaryKey = 'keys';
    
    public function getValuesAttribute($value)
    {
        return json_decode($value,true);
    }

    public function setValuesAttribute($value)
    {
        $this->attributes['values'] = json_encode($value,JSON_UNESCAPED_UNICODE);
    }

    // 全部设置
    public function getList(){
        return $this->query()->select('keys','values')->get();
    }

    // 设置信息
    public function detail($keys){
        return $this->query()->where('keys',$keys)->first();
    }

    // 设置信息
    public function getItem($keys)
    {
        return $this->query()->where('keys',$keys)->value('values');
    }
}
