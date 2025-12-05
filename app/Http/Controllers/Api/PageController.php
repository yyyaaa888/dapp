<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PageRequest;
use App\Service\Api\PageService;
use Exception;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{

    protected $app;

    public function __construct(PageService $service)
    {
        $this->app = $service;
    }

    /**
     * 主页
     * @Author   Chen
     */
    public function index(PageRequest $request): JsonResponse
    {
        try {
            return $this->app->index($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

    /**
     * 抽奖
     * @Author   Chen
     */
    public function raffle(PageRequest $request): JsonResponse
    {
        try {
            return $this->app->raffle($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }

}
