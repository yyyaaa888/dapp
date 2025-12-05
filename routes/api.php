<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace'=>'Api'],function() {
    // 主页
    Route::get('page/index','PageController@index');
    // 用户
    Route::group(['prefix' => 'user'], function (){
        // 用户信息
        Route::get('info','UserController@userInfo');
        // 用户资产
        Route::get('property','UserController@property');
        // 资产明细
        Route::get('property/log','UserController@propertyLog');
        // 用户注册
        Route::post('register','UserController@register');
        // 我的团队
        Route::get('team','UserController@team');
        // 充值
        Route::any('recharge','UserController@recharge');
        // 提现
        Route::any('withdraw','UserController@withdraw');
        // 充值记录
        Route::get('recharge/list','UserController@rechargeList');
        // 充值明细
        Route::get('recharge/log','UserController@rechargeLog');
        // 提现明细
        Route::get('withdraw/log','UserController@withdrawLog');
    });
    // 交易
    Route::group(['prefix' => 'trade'], function (){
        // 入金
        Route::post('deposit','TradeController@deposit');
    });
    // 公共
    Route::group(['prefix' => 'system'], function (){
        // 文件上传
        Route::post('upload','SystemController@uploadFile');
        // 更新价格
        Route::get('price','SystemController@updatePrice');
        // 静态收益
        Route::get('static','SystemController@staticProfit');
        // 全网分红
        // Route::get('dividend','SystemController@dividend');
        // 公告列表
        Route::get('notice/list','SystemController@noticeList');
        // 公告详情
        Route::get('notice/detail','SystemController@noticeDetail');
        // 帮助中心
        Route::get('help','SystemController@help');
        // 联系客服
        Route::get('service','SystemController@service');
    });
});