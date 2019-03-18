<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 *  加密算法
 * @param $key
 * @param $param
 * @return string
 */
function sign($key, $param)
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

/**
 * @return false|string  //当前时间
 */
function now()
{
    return date('Y-m-d H:i:s');
}


function parseSocketData($name, $data)
{
    return json_encode([$name, $data]);
}

function longPolling($callback)
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

function socket($id, $socket_name = '', $data = [])
{
    \GatewayClient\Gateway::$registerAddress = '192.168.4.146:1238';
    $data = parseSocketData($socket_name, $data);
    \GatewayClient\Gateway::sendToUid($id, $data);
}