<?php

namespace core;


class RedisDb
{
    static $redis;

    static public function instance()
    {
        if (empty(self::$redis)) {
            $redis = new \Redis();
            $redis->connect('192.168.4.146', 6379);
            $redis->auth('123456');
            self::$redis = $redis;
        }
        return self::$redis;
    }
}
