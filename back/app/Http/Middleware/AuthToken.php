<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Class AuthToken
{
	public function handle($request ,Closure $next){
		// 判断登录状态
		if (Auth::check()) {		
			// 获取已认证的用户信息		
			$user = Auth::user();
        	// 判断token
	        if (time() > strtotime($user->last_login_time) + env('APP_STATUS_TIMEOUT')){
	        	return response([
	        		'status' => 10001,
	        		'errmsg' => "Login status timed out."
	        	], 401);
	        }		
            return $next($request);
		}else{
			return response([
	        	'status' => 10001,
	        	'errmsg' => "Not login!"
	        ], 401);
		}
	}
}
