<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class TradeRequest extends BaseRequest
{
    protected $rules = [];

    protected $strings_key = [
        'address' => '用户地址',
        'deposit_type' => '投注类型',
        'deposit_amount' => '投注数量',
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
        $rules = $this->rules;
        # TODO 根据不同的情况, 添加不同的验证规则
        switch ($function['controller']) {
            case "App\Http\Controllers\Api\TradeController@deposit":
                $rules = [
                    'address' => 'required',
                    'deposit_type' => 'required|integer|in:1,2',
                    'deposit_amount' => 'required|numeric',
                ];
                break;
        }
        return $rules;
    }
}
