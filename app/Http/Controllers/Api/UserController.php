<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UserRequest;
use App\Service\Api\UserService;
use Exception;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    protected $app;

    public function __construct(UserService $service)
    {
        $this->app = $service;
    }

    /**
     * 用户信息
     * @Author   Chen
     */
    public function userInfo(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->userInfo($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 用户资产
     * @Author   Chen
     */
    public function property(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->property($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 资产明显
     * @Author   Chen
     */
    public function propertyLog(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->propertyLog($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 用户注册
     * @Author   Chen
     */
    public function register(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->register($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 我的团队
     * @Author   Chen
     */
    public function team(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->team($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 充值
     * @Author   Chen
     */
    public function recharge(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->recharge($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 提现
     * @Author   Chen
     */
    public function withdraw(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->withdraw($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 充值记录
     * @Author   Chen
     */
    public function rechargeList(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->rechargeList($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 充值明细
     * @Author   Chen
     */
    public function rechargeLog(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->rechargeLog($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 提现明细
     * @Author   Chen
     */
    public function withdrawLog(UserRequest $request): JsonResponse
    {
        try {
            return $this->app->withdrawLog($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }
}
