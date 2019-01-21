<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Lib\Snoopy;
use App\Models\User;
use App\Models\Hws_record;
use App\Models\Hws_task;
use App\Http\Controllers\CosController;

class HwsController extends Controller
{
	/**
	 * 上传作业
	 * @param string $path 文件路径
	 * @return json {status:状态码 data:服务器返回数据 errmsg:错误信息}
	 */
	public function submitWork(Request $request) {
		if (!$request->has('path')) {
			return response()->json([
				'status' => 10004,
				'errmsg' => "Invalid path."
			]);
		}

		// 获取任务时间
		$task_time = Hws_task::orderBy('id', 'desc')->first();
		if(!$task_time){
			return response()->json([
				'status' => 10005,
				'errmsg' => "No task."
			]);
		}

		if (time() < strtotime($task_time['start_time']) ||
			time() > strtotime($task_time['end_time'])){
			return response()->json([
				'status' => 10006,
				'errmsg' => "Now homework cannot be uploaded."
			]);
		}

		// 获取tid
		$tid  = $task_time['id'];
		// 获取用户
		$user = Auth::user();
		// 获取uid
		$uid  = $user['id'];

		// 插入数据
		$input = [
			'uid' => $uid,
			'tid' => $tid,
			'file_path' => $request->input('path'),
			'time' => date("Y-m-d H:i:s", time())
		];

		// 查询多次提交的作业
		$checkhws = Hws_record::where('uid', '=', $uid)->where('tid', '=', $tid)->first();
		$hws_record = Hws_record::create($input);
		
		if (!$checkhws) {
			return response()->json([
				'status' => 200
			]);
		}

		// 删除数据库
		Hws_record::where('id', '=', $checkhws['id'])->delete();
		
		// 获取服务器鉴权信息
		$cos = new CosController();
		$sign = $cos->getDeleteSign($checkhws['file_path']);

		// post数据请求COS删除文件
		$postData = ['op' => "delete"];
		$options = [
			'http' => ['method'  => 'POST',
				'Host' => "web.file.myqcloud.com",
				'header' => [
					'Content-type: application/json',
					'Authorization: '.$sign
				],
				'content' => json_encode($postData)
			]
		];
		$context = stream_context_create($options);
		$result = file_get_contents("http://web.file.myqcloud.com/files/v1/".env('COS_APPID')."/".env('COS_BUCKET_NAME')."/".$checkhws['file_path'], 0, $context);

		return response()->json([
			'status' => 200,
			'data' => $result
		]);
	}

	/**
	 * 评价作业
	 * @param int $id 作业id
	 * @param string $score 评分等级
	 * @param string $comment 评价内容
	 * @return json {status:状态码 data:作业信息}
	 */
	public function evaluateWork(Request $request, $id, $score, $comment) {
		$hws_evaluate = Hws_record::where('id','=',$id)->first();
		if (!$hws_evaluate) {
			return response()->json([
				'status' => 10007,
				'errmsg' => "Invalid id."
			]);
		}
		$hws_evaluate->score = $score;
		$hws_evaluate->comment = $comment;
		$hws_evaluate->comment_time = date("Y-m-d H:i:s", time());
		$hws_evaluate->save();

		return response()->json([
			'status' => 200,
			'data' => $hws_evaluate
		]);	
	}

	/**
	 * 显示作业
	 * @param header $token
	 * @return json {status:状态码 data:用户作业信息}
	 */
	public function showWork() {
		//获取用户
		$user = Auth::user();
		//获取数据
		$show_data = Hws_record::where('uid', '=', $user['id'])->get();
		return response()->json([
			'status' => 200,
			'data' => $show_data
		]);
	}

	/**
	 * 获取上传Sign
	 * @return json {status:状态码 data:COS上传鉴权}
	 */
	public function getSign() {
		$cos = new CosController();
		return response()->json([
			'status' => 200,
			'data' => $cos->getUploadSign(time() + env('COS_SIGN_TIMEOUT'))
		]);
	}
}