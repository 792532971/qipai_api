<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:44
 */

namespace app\home\controller;

use app\home\model\Email as EmailM;
use core\Token;
use GatewayClient\Gateway;

class Email extends Common
{
    /**
     *  邮件项目
     * @param string $token
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function inCoin($token = '', $id = 0)
    {
        if ($token == '') {
            return json(['code' => 0, 'msg' => '操作失败']);
        }
        $user_id = Token::parse($token)['id'];
        $Email = new EmailM();
        if ($id == 0) {
            $result = $Email->field('id,name,status,context,item')->where('user_id', $user_id)->select();
            return json(['code' => 1, 'data' => $result]);
        } else {
            return $this->longPolling(function () use ($id, $user_id) {
                $data = db('email')->field('id,name,status,context,item')->where('user_id', $user_id)->where('id', '>', $id)->select();
                return json([ 'code' => 1, 'data' => $data ?? (object)[] ]);
            });
        }
    }


    public function longPolling($callback)
    {
        session_write_close();  //前面的session数据存入或读取，然后关闭session.  防止session阻塞
        ignore_user_abort(false);  //停止脚本运行
        set_time_limit(30);   //设置脚本允许的时间 0:没有时间限制

        for ($i = 0; $i < 25; $i++) {
            // echo str_repeat(" ", 4000);   //把''重复4000次
            $return_data = $callback();
            if ($return_data) {
                return $return_data;
            }
            sleep(1);
            ob_flush();   //输出缓冲区的内容   //    必须和下面同时使用 flush()
            flush();  //刷新缓冲区的内容  该函数将当前为止程序的所有输出发送到用户的浏览器,必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲
        }
        ob_end_flush();  //输出缓冲区内容并关闭缓冲
        return json(['code' => 1, 'data' => (object)array()]);
    }


    /**
     *  邮件阅读
     * @param string $token
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function read($token = '', $id = 0)
    {
        $user_id = Token::parse($token)['id'];
        $Email = new EmailM();
        $result = $Email->execute("UPDATE email set status=1 WHERE user_id={$user_id} and id={$id}");
        if ($result >= 1) {
            return json(['code' => 1, 'msg' => '阅读成功']);
        } else {
            return json(['code' => 0, 'msg' => '阅读失败']);
        }
    }

    /**
     *  删除邮件
     * @param string $token
     * @param int $id
     * @return \think\response\Json
     */
    public function remove(string $token = '', $id = 0)
    {
        $Email = new EmailM();
        $userIds = Token::parse($token)['id'];
        $result = $Email->where('user_id',$userIds)->where('id',$id)->delete();
        if ($result) {
            return json(['code' => 1, 'msg' => '删除成功']);
        } else {
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }


    /**
     *  发送邮件
     * @param int $id
     * @param int $coin
     * @param string $nickname
     * @param string $photo
     */
    public function user_info($photo = '', $nickname = '', $coin = 0, $id = 0)
    {
        Gateway::$registerAddress = '192.168.31.27:1238';
        $res = [
            'id' => $id,
            'coin' => $coin,
            'nickname' => $nickname,
            'photo' => $photo
        ];
        $data = parseSocketData('user_info', $res);
        Gateway::sendToUid($id, $data);

    }


}