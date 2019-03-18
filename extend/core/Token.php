<?php

namespace core;

//use ;

class Token
{
    static public $key = 'abc123456789';

    /**
     * 生成token
     */
    static public function create(int $id)
    {
        $time = time();
        $sign = self::getSgin($id, $time);
        $token = implode('.', [$id, $time, $sign]);
        RedisDb::instance()->hset('token', $id, $token);
        return $token;
    }

    /**
     * 解析token
     */
    static public function parse(string $atoken)
    {
        $token = explode('.', $atoken);
        $id = $token[0];
        $time = $token[1];
        $sign = self::getSgin($id, $time);
        if ($sign != $token[2]) {
            print_r('解析token_err_1');
            return false;
        }

        $user_token = RedisDb::instance()->hget('token', $id);

        if ($atoken != $user_token) {

            print_r('解析token_err_2');
            return false;
        }
        return [
            'id' => $id,
            'time' => $time,
            'sign' => $sign
        ];
    }

    /**
     * 拼接
     */
    static public function getSgin($id, $time)
    {
        return md5(implode('.', [$id, $time, self::$key]));
    }
}
