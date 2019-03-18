<?php

namespace app\home\controller;

use think\Controller;

class Common extends Controller
{
    public function sign($key, $param)
    {
        ksort($param);
        $paramArr = [];
        foreach ($param as $k => $v) {
            $paramArr[] = $k . '=' . $v;
        }
        $sign_str = $key . "&" . implode('&', $paramArr);
        $sign = md5($sign_str);
        return $sign;
    }
}
