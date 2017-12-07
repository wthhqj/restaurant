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

//dingo
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {
    $api->group(['prefix'=>'v1', 'namespace'=> '\App\Http\Controllers\Api\V1'], function ($api){
        /**增加/更新员工接口*/
        $api->post('user/add', ['as'=>'user.add', 'uses'=>'UserController@add']);
        /**删除员工*/
        $api->post('user/delete', ['as'=>'user.del', 'uses'=>'UserController@del']);
        /**登陆接口*/
        $api->post('user/login', ['as'=>'user.login', 'uses'=>'UserController@login']);
        /**修改密码接口*/
        $api->post('resetPassword', ['as'=>'user.changePwd', 'uses'=>'UserController@changePwd']);
        /**获取用户详细信息*/
        $api->get('user', ['as'=>'user', 'uses'=>'UserController@get']);
        /**获取员工列表*/
        $api->get('user/list', ['as'=>'user', 'uses'=>'UserController@getList']);

        /**增加/更新菜品*/
        $api->post('food/add', ['as'=>'food.add', 'uses'=>'FoodController@add']);
        /**删除菜品*/
        $api->post('food/del', ['as'=>'food.del', 'uses'=>'FoodController@del']);
        /**切换菜品上下架状态*/
        $api->post('food/status', ['as'=>'food.status', 'uses'=>'FoodController@status']);
        /**查询菜品*/
        $api->get('food/search', ['as'=>'food.search', 'uses'=>'FoodController@search']);
        /**获取上架的菜品列表*/
        $api->get('food/list', ['as'=>'food.list', 'uses' => 'FoodController@getList']);

        /**获取空桌号*/
        $api->get('desk/', ['as'=>'desk', 'uses'=>'DeskController@get']);

        /**下单接口*/
        $api->post('order/add', ['as'=>'order.add', 'uses'=>'OrderController@add']);
        /**获取订单接口*/
        $api->get('order/list', ['as'=>'order.list', 'uses'=>'OrderController@getList']);
        /**获取订单详情*/
        $api->get('order/detail', ['as'=>'order.detail', 'uses'=>'OrderController@detail']);
        /**获取一周的成交额*/
        $api->get('getMoneyInfo', ['as'=>'order.weekCount', 'uses'=>'OrderController@getMoneyInfo']);
        /**获取一周订单的数量*/
        $api->get('getChartInfo', ['as'=>'order.weekPrice', 'uses'=>'OrderController@getChartInfo']);

        /**图片上传*/
        $api->post('img/upload', ['as'=>'img.upload', 'uses'=>'UserController@imgUpload']);
    });
});

