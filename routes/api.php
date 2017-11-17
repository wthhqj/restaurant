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
        /**获取用户详细信息*/
        $api->get('user', ['as'=>'user', 'uses'=>'UserController@get']);

        /**增加/更新菜品*/
        $api->post('food/add', ['as'=>'food.add', 'uses'=>'FoodController@add']);
        /**删除菜品*/
        $api->post('food/del', ['as'=>'food.del', 'uses'=>'FoodController@del']);
        /**切换菜品上下架状态*/
        $api->post('food/status', ['as'=>'food.status', 'uses'=>'FoodController@status']);
        /**查询菜品*/
        $api->get('food/search', ['as'=>'food.search', 'uses'=>'FoodController@search']);
        /**获取上架的菜品列表*/
        $api->get('food/list', ['as'=>'food.list', 'uses' => 'FOodController@getList']);
    });
});

