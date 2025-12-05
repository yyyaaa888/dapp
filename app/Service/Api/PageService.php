<?php

namespace App\Service\Api;

use App\Service\BaseService;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\App;
use App\Service\Api\Factory;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class PageService extends BaseService
{
    /**
     * 主页
     * @Author   Chen
     */
    public function index($request)
    {
        // 是否注册
        $data['is_register'] = 0;
        // USDT余额
        $data['usdt'] = 0;
        // PEPE余额
        $data['pepe'] = 0;
        // PEPE余额
        $data['wbsc'] = 0;
        // 公告
        $data['notice'] = App::make('notice')->where('status',1)->orderBy('notice_id','desc')->first();
        // 用户级别
        $data['grade_id'] = 0;
        // 用户级别
        $data['grade_name'] = '';
        // 是否入金
        $data['is_deposit'] = 0;
        $user = App::make('user')->details(['address'=>$request['address']]);
        if($user){
            $data['is_register'] = 1;
            $data['usdt'] = $user['usdt'];
            $data['pepe'] = $user['pepe'];
            $data['wbsc'] = $user['wbsc'];
            $data['grade_name'] = $user['grade_name'];
            $data['is_deposit'] = $user['is_deposit'];
        }
        $data['notice'] = App::make('notice')->where('status',1)->orderBy('notice_id','desc')->first();
        $data['banner_list'] = App::make('banner')->getListAll();
        return $this->success('获取成功',$data);
    }
}
