<?php

namespace app\home\validate;

use think\Validate;

/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-20
 * Time: 16:49
 */
class User extends Validate
{
    protected $rule = [
        'username' => 'require|unique:user',
        'nickname' => 'require|unique:user',
        'password' => 'require',
    ];

    protected $message = [
        'username.require' => '用户名不能为空',
        'password.require' => '密码不能为空',
        'username.unique' => '账号已存在',
        'nickname.unique' => '昵称已存在',
        'nickname.require' => '昵称不能为空'
    ];

    protected $scene = [
        'register' => ['username', 'nickname'],
    ];

}