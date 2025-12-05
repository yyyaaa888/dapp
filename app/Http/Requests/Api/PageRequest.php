<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class PageRequest extends BaseRequest
{
    protected $rules = [];

    protected $strings_key = [
        'address' => '用户地址',
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
            case "App\Http\Controllers\Api\PageController@index":
            case "App\Http\Controllers\Api\PageController@raffle":
                $rules = [
                    'address' => 'required',
                ];
                break;
        }
        return $rules;
    }
}
