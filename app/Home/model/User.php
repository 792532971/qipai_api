<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-20
 * Time: 15:59
 */

namespace app\home\model;


use think\Model;

class User extends Model
{
    /**
     *  用户登录验证获取id
     * @param $username
     * @param $password
     * @return int|mixed
     */
    public function getUserToken($username, $password)
    {
        $passwordo = md5($password);
        $data = $this->where('username', $username)->find();
        if ($data) {
            if ($data['password'] == $passwordo) {
                if ($data['lock'] == 1) {
                    return '禁用';  //用户被禁用
                }
                $this->where('id', $data['id'])->update(['active_time' => date('Y-m-d H:i:s', time())]);
                return $data['id'];  //返回id
            } else {
                return '密码错误'; //密码错误
            }
        } else {
            return '用户不存在';   //用户不存在
        }

    }

    /**
     *  保存用户信息生成token
     * @param $nickname
     * @param $username
     * @param $password
     * @param $sex
     * @return mixed
     */
    public function generateToken($nickname, $username, $password, $sex)
    {
//        $maxRgt = $this->max('rgt');
//        $lft = $maxRgt + 1;
//        $rgt = $maxRgt + 2;
//        $data['lft'] = $lft;
//        $data['rgt'] = $rgt;    更新左右值
        $data = ['nickname' => $nickname, 'username' => $username, 'sex' => $sex, 'password' => $password, 'create_time' => date('Y-m-d H:i:s', time()), 'create_ip' => request()->ip()];
        $result = $this->create($data);
        return $result->id;
    }

    /**
     *  获取等级排行榜
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getUserLevel()
    {
        $data = $this->field('nickname,vip_exp')->order('vip_exp desc')->select();
        return $data;
    }

    static function dis($vip_exp, $exp_num)
    {
        $dis = round(($vip_exp / $exp_num) * 100);
        return $dis;
    }


}