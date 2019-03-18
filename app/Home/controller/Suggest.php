<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:31
 */

namespace app\home\controller;

use app\home\model\Suggest as SuggestM;
use core\Token;

class Suggest extends Common
{
    public function send(string $token = '', string $content = '')
    {
        $user_id = Token::parse($token)['id'];
        if (empty($content)) {
            return ['code' => 0, 'msg' => '内容不能为空'];
        }
        $Suggest = new SuggestM();
        $result = $Suggest->sendContent($user_id, $content);
        if ($result) {
            return ['code' => 1, 'msg' => '邮件发送成功'];
        } else {
            return ['code' => 0, 'msg' => '邮件发送失败'];
        }
    }
}