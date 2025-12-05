<?php

namespace App\Http\Requests\Api;

use App\Http\Requests\BaseRequest;

class SystemRequest extends BaseRequest
{
    protected $rules = [];

    protected $strings_key = [
        'notice_id' => '公告id',
        'page' => '页码',
        'page_size' => '每页数量',
        'type' => '上传类型',
        'file' => '上传文件',
    ];

    public function rules(): array
    {
        $function = request()->route()->getAction();
        $rules = $this->rules;
        # TODO 根据不同的情况, 添加不同的验证规则
        switch ($function['controller']) {
            case "App\Http\Controllers\Api\SystemController@noticeList":
                $rules = [
                    'page' => 'required|integer',
                    'page_size' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\SystemController@noticeDetail":
                $rules = [
                    'notice_id' => 'required|integer',
                ];
                break;
            case "App\Http\Controllers\Api\SystemController@uploadFile":
                $rules = [
                    'type'=>'required|in:1,2',
                    'file'=>'required',
                ];
                break;
        }
        return $rules;
    }
}
