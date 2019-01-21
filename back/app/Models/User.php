<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * 可以被批量赋值的属性
     * @var array
     */
    protected $fillable = [
        'name', 'last_login_time',
    ];

    /**
     * 在数组中想要隐藏的属性
     * @var array
     */
    protected $hidden = [
        'password', 'token',
    ];

    /**
     * 该模型是否被自动维护时间戳
     * @var boolean
     */
    /*
        Eloquent期望表中存在created_at和updated_at两个字段
        字段类型为timestamp
        如果不希望这两个字段的话
        设置$timestamps为false
     */
    public $timestamps = false;

    /**
     * 与模型关联的数据表
     * @var string
     */
    protected $table = 'common_user';
}
