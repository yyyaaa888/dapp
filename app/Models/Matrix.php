<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;

class Matrix extends Model
{
	// 矩阵
    protected $table = 'matrix';

    // 表主键
    protected $primaryKey = 'matrix_id';

    // 在数组中隐藏的属性
    protected $hidden = ['updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','grade_id','sort','is_out'];

    //追加到模型数组表单的访问器
    protected $appends = ['grade_name'];

    public function getGradeNameAttribute()
    {
        if(isset($this->attributes['grade_id'])){
            return $this->attributes['grade_id'] > 0 ? App::make('user')->userGrade()[$this->attributes['grade_id']] : '';
        }
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['matrix_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    // 加入矩阵
    public function joinMatrix($address,$grade_id)
    {
        $matrix = App::make('matrix')->where(['address'=>$address,'grade_id'=>$grade_id])->first();
        if(!$matrix){
            $sort = App::make('matrix')->where('grade_id',$grade_id)->count();
            App::make('matrix')->create([
                'address'=>$address,
                'grade_id'=>$grade_id,
                'sort'=>$sort + 1,
            ]);
            App::make('user')->where('address',$address)->update(['grade_id'=>$grade_id]);
        }
    }

    // 出局矩阵
    public function outMatrix($basicSet)
    {
        $grade = App::make('user')->userGrade();
        foreach ($grade as $grade_id => $name) {
            if($grade_id > 1){
                switch ($grade_id) {
                    case 2: // 创业者
                        $multiple = 30;
                        break;
                    case 3: // 白银
                        $multiple = 14;
                        break;
                    case 4: // 黄金
                        $multiple = 11;
                        break;
                    case 5: // 钻石
                        $multiple = 2;
                        break;
                    case 6: // 皇冠
                        $multiple = 9999;
                        break;
                }
                $count = App::make('matrix')->where(['grade_id'=>$grade_id])->count();
                $layer = $count > 0 ? bcdiv($count, $multiple, 0) : 0;
                if($layer > 0){
                    $total = $layer * $multiple + 1;
                    if($count == $total){
                        $list = App::make('matrix')->where(['grade_id'=>$grade_id])->orderBy('sort','asc')->limit($layer)->get();
                        $target = $list[count($list)-1];
                        if(!empty($target)){
                            App::make('matrix')->where('matrix_id',$target['matrix_id'])->update(['is_out'=>1]);
                            $amount = $basicSet['grade_'.$grade_id.'_amount'];
                            if(!$target['is_out'] && $amount > 0){
                                Factory::UserWalletLoglService()->add($target['address'], 0, 301, $amount, $name.'奖励：' . $amount);
                            }
                            $new_grade_id = $grade_id + 1;
                            if(isset($grade[$new_grade_id])){
                                $this->joinMatrix($target['address'],$new_grade_id);
                            }
                        }
                    }
                }
            }
        }
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
