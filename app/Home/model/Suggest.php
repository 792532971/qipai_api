<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:40
 */

namespace app\home\model;

use core\Token;
use think\cache\driver\Redis;
use think\Model;

class Suggest extends Model
{
    public static function sendContent($user_id, $content)
    {
        $data = ['content' => $content, 'create_time' => date('Y-m-d H:i:s', time()), 'user_id' => $user_id];
        $result = self::insert($data);
        return $result;
    }
}