<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    // return $app->version();
    return "Hello World! - API v1.1.2 [Copyright &copy; WangYuanStudio]";
});

// 用户登录
$app->get('login/{name}/{password}', 'UserController@login');

// 获取登录信息
$app->get('info', [
	'middleware' => 'auth',
	'uses' => 'UserController@info'
]);

// 登出
$app->get('logout', [
	'middleware' => 'auth',
	'uses' => 'UserController@logout'
]);

// 上传作业
$app->post('submitwork', [
	'middleware' => 'auth',
	'uses' => 'HwsController@submitWork'
]);

// 评价作业
$app->post('evaluatework/{id}/{score}/{comment}', 'HwsController@evaluateWork');

// 显示作业
$app->get('showwork', [
	'middleware' => 'auth',
	'uses' => 'HwsController@showWork'
]);

// 获取上传Sign
$app->get('getUploadSign', [
	'middleware' => 'auth',
	'uses' => 'HwsController@getSign'
]);

// 密文输出
$app->get('encrypt/{password}', 'UserController@exportPwd');
