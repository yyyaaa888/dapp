<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SystemRequest;
use App\Service\Api\SystemService;
use Exception;
use Illuminate\Http\JsonResponse;

class SystemController extends Controller
{

    protected $app;

    public function __construct(SystemService $service)
    {
        $this->app = $service;
    }

    /**
     * 获取价格
     * @Author   Chen
     */
    public function updatePrice(SystemRequest $request): JsonResponse
    {
        try {
            return $this->app->updatePrice($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 全网分红
     * @Author   Chen
     */
    public function dividend(): JsonResponse
    {
        try {
            return $this->app->dividend();
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 静态收益
     * @Author   Chen
     */
    public function staticProfit(): JsonResponse
    {
        try {
            return $this->app->staticProfit();
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 公告列表
     * @Author   Chen
     */
    public function noticeList(SystemRequest $request): JsonResponse
    {
        try {
            return $this->app->noticeList($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 公告详情
     * @Author   Chen
     */
    public function noticeDetail(SystemRequest $request): JsonResponse
    {
        try {
            return $this->app->noticeDetail($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 帮助中心
     * @Author   Chen
     */
    public function help(): JsonResponse
    {
        try {
            return $this->app->help();
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 联系客服
     * @Author   Chen
     */
    public function service(): JsonResponse
    {
        try {
            return $this->app->service();
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 文件上传
     * @Author   Chen
     */
    public function uploadFile(SystemRequest $request): JsonResponse
    {
        try {
            return $this->app->uploadFile($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 同步用户
     * @Author   Chen
     */
    public function task()
    {
        try {
            return $this->app->task();
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }
}
