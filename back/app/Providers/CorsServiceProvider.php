<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use App\Http\Middleware\EnableCrossRequest;

class CorsServiceProvider extends ServiceProvider
{
	/**
     * 指定提供者加载是否延缓
     *
     * @var bool
     */
	protected $defer = false;

    /**
     * 注册服务提供者
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * 启动任意应用服务
     *
     * @return void
     */
    public function boot() {
        $request = app(Request::class);
        $this->app->options($request->path(), function() {
        	return response('OK', 200);
        });
        $this->app->middleware([EnableCrossRequest::class]);
    }
}
