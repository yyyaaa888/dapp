<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->post('upload', 'HomeController@uploadFile');

    // 用户列表
    $router->resource('user/list', 'UserController');
    // 会员等级
    $router->resource('user/grade', 'GradeController');

    // 入金列表
    $router->resource('deposit/list', 'DepositController');
    
    // USDT充值
    $router->resource('finance/usdt/recharge', 'UsdtRechargeController');
    // WBSC充值
    $router->resource('finance/wbsc/recharge', 'WbscRechargeController');
    // 提现记录
    $router->resource('finance/withdraw', 'WithdrawController');
    // USDT明细
    $router->resource('finance/usdt/log', 'UsdtLogController');
    // WBSC明细
    $router->resource('finance/wbsc/log', 'WbscLogController');
    
    // 基础设置
    $router->resource('setting/basic', 'BasicSetController');
    // 基础设置
    $router->resource('setting/banner', 'BannerController');
    // 系统公告
    $router->resource('setting/notice', 'NoticeController');
    // 帮助中心
    $router->resource('setting/help', 'HelpSetController');
    // 联系客服
    $router->resource('setting/service', 'ServiceSetController');
});
