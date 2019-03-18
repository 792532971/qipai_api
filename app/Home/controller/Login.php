<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-20
 * Time: 15:42
 */

namespace app\home\controller;


use app\home\model\User;
use core\RedisDb;
use core\Token;
use think\cache\driver\Redis;
use think\Loader;

class Login extends Common
{

    /**
     *   登录获取token
     * @param string $username
     * @param string $password
     * @return \think\response\Json
     */
    public function login(string $username = '', string $password = '')
    {
        $User = new User();
        $result = $User->getUserToken($username, $password);
        if ($result == '用户不存在') {
            return json(['code' => 0, 'msg' => '用户不存在']);
        } elseif ($result == '密码错误') {
            return json(['code' => 0, 'msg' => '密码错误']);
        } elseif ($result == '禁用') {
            return json(['code' => 0, 'msg' => '用户被禁用']);
        } else {
            $_token = Token::create($result);
            return json(['code' => 1, 'msg' => 'success', 'token' => $_token]);
        }

    }

    /**
     *   注册获取token
     * @param string $nickname
     * @param string $username
     * @param string $password
     * @param string $password2
     * @param int $sex
     * @return \think\response\Json
     */
    public function register(string $nickname = '', $username = '', $password = '', $password2 = '', int $sex = 0)
    {
        $User = new User();
        $data = ['username' => $username, 'nickname' => $nickname, 'password' => $password, 'password2' => $password2, 'sex' => $sex];
        $validate = Loader::validate('User');
        if (!$validate->scene('register')->check($data)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }
        if ($password !== $password2) {
            return json(['code' => 0, 'msg' => '两次密码不一致']);
        }
        $password = md5($password);
        $result = $User->generateToken($nickname, $username, $password, $sex);
        $_token = Token::create($result);
        return json(['code' => 1, 'msg' => 'success', 'token' => $_token]);

    }

    /**
     * 游客登录获取Token
     */
    public function lookRegister()
    {
        // 游客登录生成随机token
        $numbers = range(10, 99);
        shuffle($numbers);
        $code = array_slice($numbers, 0, 4);
        $ordCode = $code[0] . $code[1] . $code[2] . $code[3];
        $token = Token::create($ordCode);
        return [$token];
    }

    /**
     *  Token登录
     * @param string $token
     * @return \think\response\Json
     */
    public function tokenAutoLogin($token = '')
    {
        $userId = Token::parse($token)['id'];
        if (User::get($userId) !== null) {
            return json(['code' => 1, 'msg' => '登陆成功']);
        } else {
            return json(['code' => 0, 'msg' => '登陆失败']);
        }
    }

    /**
     *  退出登录
     * @return \think\response\Json
     */
    public function outLogin()
    {
        RedisDb::instance()->delete('token');
    }

}