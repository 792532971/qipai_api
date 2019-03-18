<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2019-1-17
 * Time: 11:28
 */

namespace app\Home\controller;

use core\Token;

/**
 *  客服类
 * Class CustomerService
 * @package app\Home\controller
 */
class CustomerService extends Common
{
    public function _initialize()
    {
        $this->cusModel = new \app\Home\model\CustomerService();
    }

    /**
     *  用户发来的消息
     * @param string $token
     * @param string $content
     * @return \think\response\Json
     */
    public function sendMessage($token = '', $content = '')
    {
        if (empty($token)) {
            return json(['code' => 0, 'data' => '请登录']);
        }
        $userId = Token::parse($token)['id'];
        if (empty($content)) {
            return json(['code' => 0, 'data' => '请输入内容']);
        }
        $data = [
            'user_id' => $userId,
            'content' => $content,
            'send_time' => now()
        ];
        $result = $this->cusModel->save($data);
        if ($result) {
//            $res = [
//                'user_id' => $userId,
//                'content' => $content,
//                'time' => now()
//            ];
//            socket($userId, 'get_message', $res);
            return json(['code' => 1, 'data' => '发送成功']);

        } else {
            return json(['code' => 0, 'data' => '发送失败']);
        }
    }

    public function messageList($token = '10002.1546594735.0af57a61e936a0e633788645a6db7f38')
    {
        $userId = Token::parse($token)['id'];
        $data = $this->cusModel->where('user_id', $userId)->field('user_id,content,send_time')->limit(10)->select();
        return json(['code' => 1, 'data' => $data]);

    }


}