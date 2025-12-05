<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class UserRequest extends BaseRequest
{
    protected $rules = [];

    protected $strings_key = [
        'address' => '用户地址',
        'up_address' => '上级地址',
        'trade_type' => '交易类型',
        'wallet_type' => '账户类型',
        'keyword' => '搜索关键词',
        'recharge_amount' => '充值数量',
        'recharge_voucher' => '充值凭证',
        'withdraw_type' => '提现类型',
        'withdraw_address' => '提现地址',
        'withdraw_amount' => '提现数量',
        'page' => '页码',
        'page_size' => '每页数量',
    ];

    /**
     * @Author   Chen
     * @DateTime 2022-05-23
     */
    public function rules(): array
    {
        $function = request()->route()->getAction();
        $method = request()->method();
        $rules = $this->rules;
        # TODO 根据不同的情况, 添加不同的验证规则
        switch ($function['controller']) {
            case "App\Http\Controllers\Api\UserController@team":
                $rules = [
                    'address' => 'required',
                    'page' => 'required|integer',
                    'page_size' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@property":
                $rules = [
                    'address' => 'required',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@register":
                $rules = [
                    'address' => 'required',
                    'up_address' => 'required',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@propertyLog":
                $rules = [
                    'address' => 'required',
                    'wallet_type' => 'required|integer|in:0,1,2',
                    'trade_type' => 'required|integer|in:0,1,2,3,4,5,6',
                    'page' => 'required|integer',
                    'page_size' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@withdrawLog":
            case "App\Http\Controllers\Api\UserController@rechargeList":
                $rules = [
                    'address' => 'required',
                    'page' => 'required|integer',
                    'page_size' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@rechargeLog":
                $rules = [
                    'address' => 'required',
                    'wallet_type' => 'required|integer|in:0,1,2',
                    'page' => 'required|integer',
                    'page_size' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@userInfo":
                $rules = [
                    'address' => 'required',
                ];
                break;
            case "App\Http\Controllers\Api\UserController@recharge":
                if($method == 'POST'){
                    $rules = [
                        'address' => 'required',
                        'recharge_amount' => 'required|numeric|min:0.001',
                        'recharge_voucher' => 'required|url',
                    ];
                }
                break;
            case "App\Http\Controllers\Api\UserController@withdraw":
                if($method == 'POST'){
                    $rules = [
                        'address' => 'required',
                        'withdraw_type' => 'required|integer|in:1,2',
                        // 'withdraw_address' => 'required',
                        'withdraw_amount' => 'required|numeric|min:0.001',
                    ];
                }
                break;
        }
        return $rules;
    }
}
