<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;

class User extends Authenticatable implements JWTSubject
{
    // 用户表
    protected $table = 'user';

    // 表主键
    protected $primaryKey = 'user_id';

    // 在数组中隐藏的属性
    protected $hidden = ['password','updated_at'];

    // 可以被批量赋值的属性
    protected $fillable = ['address','up_address','grade_id','usdt','pepe','wbsc','team_num','direct_num','personal_amount','team_amount','max_amount','is_deposit','is_node','is_profit_end','is_stop_profit','is_stop_withdraw'];

    //追加到模型数组表单的访问器
    protected $appends = ['grade_name','total_amount'];

    public function getGradeNameAttribute()
    {
        if(isset($this->attributes['grade_id'])){
            return $this->attributes['grade_id'] > 0 ? $this->userGrade()[$this->attributes['grade_id']] : '';
        }
    }

    public function getTotalAmountAttribute()
    {
        if(isset($this->attributes['personal_amount']) && isset($this->attributes['team_amount'])){
            return bcadd($this->attributes['personal_amount'], $this->attributes['team_amount'], 3);
        }
    }

    // 用户状态
    public function enable()
    {
        return [
            '0'=> '停用',
            '1'=> '启用'
        ];
    }


    // 用户级别
    public function userGrade()
    {
        return [
            '1'=> 'V1',
            '2'=> 'V2',
            '3'=> 'V3',
            '4'=> 'V4',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    //关联所有直推
    public function direct() {
        return $this->hasMany(User::class, 'up_address', 'address');
    }

    //关联等级
    public function grade(){
        return $this->hasOne(UserGrade::class,'grade_id', 'grade_id');
    }

    //列表
    public function getList($where, $with = [], $page = 1, $page_size = 15)
    {
        return $this->query()->orderBy('user_id','desc')->paginate($page_size, '*', '', $page);
    }

    //列表
    public function getListAll()
    {
        return $this->query()->orderBy('user_id','desc')->get();
    }

    //详情
    public function details($where, $with = [])
    {
        $filter = [];
        if(is_array($where)){
            $filter = array_merge($filter, $where);
        }else{
            $filter['user_id'] = (int)$where;
        }
        return $this->query()->with($with)->where($filter)->first();
    }

    //直推列表
    public function getDirectList($where, $with = [], $page = 1, $page_size = 15) {
        $model = $this->query();
        $model = $model->with($with);
        $model = $model->select('address', 'grade_id','personal_amount','team_amount','created_at');
        //筛选：上级地址
        if (isset($where['up_address'])&& $where['up_address'] != '') {
            $model = $model->where('up_address', $where['up_address']);
        }
        //筛选：用户地址
        if (isset($where['keyword'])&& trim($where['keyword']) != '') {
            $model = $model->where('address','like',"%{$where['keyword']}%");
        }
        $model = $model->orderBy('user_id', 'desc');
        $model = $model->paginate($page_size, '*', '', $page)->toArray();
        return $model;
    }

    /**
     * 所有上级团队
     * @param $user_id
     */
    public function allSuperior($address, &$superior = [])
    {
        if($address){
            $user = App::make('user')->details(['address'=>$address]);
            if($user){
                array_push($superior, $user->toArray());
                $up_address = $user['up_address'];
                if(!empty($up_address)){
                    $this->allSuperior($up_address,$superior);
                }
            }
        }
        return $superior;
    }

    // 所有接点下级
    public function getTeam($address = [], &$subordinate = [])
    {
        $user = App::make('user')
                ->select('address','up_address')
                ->whereIn('up_address',$address)
                ->orderBy('user_id','asc')
                ->get()
                ->toArray();
        if(!empty($user)){
            $addressArr = array_column($user,'address');
            foreach ($user as $item){
                array_push($subordinate,$item);
            }
            $this->getTeam($addressArr, $subordinate);
        }
        return $subordinate;
    }

    // 结算
    public function settleReward($address)
    {
        $basicSet = App::make('setting')->getItem('basic');
        $user = App::make('user')->details(['address'=>$address]);
        if($user){
            // 更新入金
            $this->setDeposit($user);
            // 上级升级
            $this->upGrade($user,$basicSet);
        }
    }

    // 更新入金
    public function setDeposit($user)
    {
        if($user && !$user['is_deposit']){
            $user->grade_id = 1;
            $user->is_deposit = 1;
            $user->save();
        }
    }

    // 上级升级
    public function upGrade($user,$basicSet)
    {
        if($user['up_address']){
            $superior = App::make('user')->where('address',$user['up_address'])->first();
            if($superior && $superior['grade_id'] == 1){
                $effectiveNum = App::make('user')->where(['up_address'=>$superior['address'],'is_deposit'=>1,'grade_id'=>1])->count();
                if($effectiveNum >= 2){
                    $superior->grade_id = 2;
                    $superior->save();
                    // 加入矩阵
                    App::make('matrix')->joinMatrix($superior['address'],2);
                    // 检测出局
                    App::make('matrix')->outMatrix($basicSet);
                }
            }
        }
    }

    // 发放推荐奖励
    public function grantReward($address)
    {
        $basicSet = App::make('setting')->getItem('basic');
        $user = App::make('user')->details(['address'=>$address]);
        if($user && $user['up_address']){
            $superior = App::make('user')->where('address',$user['up_address'])->first();
            if($superior){
                $logNum = App::make('userProfitLog')->where(['address'=>$superior['address'],'log_type'=>301])->count();
                if($logNum < 2){
                    $amount = $basicSet['one_two_direct'];
                }else{
                    $amount = $basicSet['three_direct'];
                }
                if($amount > 0){
                    Factory::UserWalletLoglService()->add($superior['address'], 0, 301, $amount, '来源用户地址：' . $user['address']);
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
