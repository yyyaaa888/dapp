<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TradeRequest;
use App\Service\Api\TradeService;
use Exception;
use Illuminate\Http\JsonResponse;

class TradeController extends Controller
{

    protected $app;

    public function __construct(TradeService $service)
    {
        $this->app = $service;
    }

    /**
     * 入金
     * @Author   Chen
     */
    public function deposit(TradeRequest $request): JsonResponse
    {
        try {
            return $this->app->deposit($request);
        } catch (Exception $e) {
            return $this->app->fail($e->getMessage());
        }
    }
}
