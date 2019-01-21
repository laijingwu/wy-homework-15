<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class UserController extends Controller
{
	/**
	 * 用户登录
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @return json {status:状态码 errmsg:错误信息 data:Token}
	 */
	public function login(Request $request, $name, $password) {
		$name = urldecode($name);
		$password = urldecode($password);

		// 数据库查询用户
		$user = User::where('name', $name)->first();
		if (!$user) {
			// 不存在用户
			return response()->json([
				'status' => 10002,
				'errmsg' => "Invalid username."
			]);
		}

		// 判断密码是否一致
		if ($password != Crypt::decrypt($user['password'])) {
			return response()->json([
				'status' => 10003,
				'errmsg' => "Incorrect password."
			]);
		}

		// 更新用户token
		$token = str_random(32);
		$user->token = $token;
		// 更新用户登录时间
		$user->last_login_time = date("Y-m-d H:i:s", time());
		$user->save();
		
		return response()->json([
			'status' => 200,
			'data' => $user->token
		]);
	}
	/**
	 * 获取用户信息
	 * @param header $token
	 * @return json {status:状态码 data:用户数据}
	 */
	public function info(){
		return response()->json([
			'status' => 200,
			'data' => Auth::user()
		]);
	}	
	
	/**
	 * 登出
	 * @param header $token
	 * @return json {status:状态码}
	 */
	public function logout(){
		//获取用户
		$user = Auth::user();
		$user->token = null;
		$user->save();

		return response()->json(['status' => 200]);
	}

	/**
	 * 输出密文
	 * @param  string $password 明文
	 * @return 密文
	 */
	public function exportPwd(Request $request, $password){
		return Crypt::encrypt($password);
	}	
}
