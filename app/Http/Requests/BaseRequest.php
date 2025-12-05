<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @var string[]
     */
    protected $strings_val = [
        'required' => '为必填项',
        'min' => '最小为:min',
        'max' => '最大为:max',
        'between' => '长度在:min和:max之间',
        'integer' => '必须为整数',
        'exists' => '不存在',
        'unique' => '已存在',
        'regex' => '验证不通过',
        'in' => '不在可选范围',
        'required_if' => '必填',
        'numeric' => '格式不符合',
        'not_in' => '格式不符合',
        'string' => '格式不符合',
        'present' => '缺少参数',
        'filled' => '参数不能为空',
        'alpha_num' => '参数为字母、数字组合',
        'image' => '图片格式有误',
        'json' => '参数为JSON格式',
        'exists' => '查询无数据',
        'same' => '参数不一致',
        'date' => '日期格式有误',
        'nullable' => '可选项',
        'url' => 'Url不符合',
    ];

    /**
     * @Author   Chen
     */
    public function messages(): array
    {
        $array = [];
        $rules = $this->rules();
        if ($rules) {
            $k_array = $this->strings_key;
            $v_array = $this->strings_val;
            foreach ($rules as $key => $value) {
                $new_arr = explode('|', $value);
                foreach ($new_arr as $k => $v) {
                    $head = strstr($v, ':', true);
                    if ($head) {
                        $v = $head;
                    }
                    $array[$key.'.'.$v] = $k_array[$key].$v_array[$v];
                }
            }
        }
        return $array;
    }

    /**
     * @Author   Chen
     */
    public function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw (new HttpResponseException(response()->json([
            'code' => 0,
            'message' => !empty($errors) ? $errors[0] : '请求参数有误',
            'data' => [],
        ], 200)));
    }
}
