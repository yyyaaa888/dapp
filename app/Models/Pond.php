<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pond extends Model
{
    // 池子设置
    protected $table = 'pond';

    // 表主键
    protected $primaryKey = 'pond_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['amount','add_amount','reduce_amount','pond_date'];

    // 补偿池子
    public function getCompenPond(){
        return $this->query()->where('pond_id',1)->first();
    }

    // 大单池子
    public function getBigPond(){
        return $this->query()->where('pond_id',2)->first();
    }
}
