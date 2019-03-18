<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:46
 */

namespace app\home\model;

use core\Token;
use think\Db;
use think\Model;

class Email extends Model
{
    /**
     *  获取邮件列表
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getEmailList($user_id)
    {
        $data = self::field('id,name,status,context,item')->where('user_id', $user_id)->select();
        return $data;
    }

    /** 邮件已读未读更新
     * @param $ids
     * @return $this
     */
    public function updateEmail($ids)
    {
        $id = implode(',', $ids);
        $res = self::where('id', 'in', $id)->update(['status' => 1]);
        return $res;
    }

    /**
     *  邮件删除
     * @param $userIds
     * @param $id
     * @return int
     */
    public function deleteEmail($userIds, $id)
    {
        $result = self::where('id', 'in', $id)->where('user_id', $userIds)->delete();
        return $result;
    }

    /** 根据token获取邮件列表id
     * @param $token
     * @return array
     */
    public function getParseToken($token)
    {
        $parse = new Token();
        $tok = $parse->parse($token);
        $id = $tok['id'];
        $_data = Db::table('email')->where('user_id', $id)->field('id')->select();
        $arr = [];
        foreach ($_data as $data) {
            $arr[] = $data['id'];
        }
        return $arr;
    }
}