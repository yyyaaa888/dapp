<?php

namespace App\Service;

use Illuminate\Http\JsonResponse;
use App\Service\Factory;
use Exception;

class BaseService
{
    /**
     * 返回成功 Json
     * @Author   Chen
     */
    public function success($message = '成功', $data = []): JsonResponse
    {
        return response()->json([
            'code' => 1,
            'message' => __($message),
            'data' => $data,
        ]);
    }

    /**
     * 返回失败 Json
     * @Author   Chen
     */
    public function fail($message = '失败', $data = []): JsonResponse
    {
        return response()->json([
            'code' => 0,
            'message' =>  __($message),
            'data' => $data,
        ]);
    }

    /**
     * 验证数组是否为空
     * @Author   Chen
     */
    public function checkIsNull($data, $message = '')
    {
        if (!$data) {
            throw new Exception($message ? $message : '没有数据');
        }
    }

    /**
     * 错误提示
     * @Author   Chen
     */
    public function errorMsg($tips)
    {
        throw new Exception( __($tips));
    }
}
