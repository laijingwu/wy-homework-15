<?php

namespace App\Http\Middleware;

use Closure;

class EnableCrossRequest
{
	public function handle($request ,Closure $next){
        $response = $next($request);
        $response->header('Access-Control-Allow-Origin', env('APP_ALLOW'));
        
		if ($request->isMethod('OPTIONS')) {
            $response->header('Access-Control-Allow-Headers', 'token, Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
            $response->header('Access-Control-Allow-Credentials', 'true');
		}
        return $response;
	}
}
