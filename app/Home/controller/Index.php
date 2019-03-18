<?php

namespace app\home\controller;

use app\Home\model\TopInfo;

header('Access-Control-Allow-Origin:*');
class Index extends Common
{
    public function index()
    {
        return 'api';
    }

    /**
     *  获取系统顶部公告信息
     * @param int $id
     * @return \think\response\Json
     */
    public function getConfig($id = 0)
    {
        if ($id == 0) {
            $result = db('top_info')->where('type', 0)->field('id,information as sys_notice')->order('create_time desc')->limit(10)->select();
            return json(['code' => 1, 'data' => $result]);
        } else {
            return $this->longPolling(function () use ($id) {
                $data = TopInfo::where('type', 0)->where('id', '>', $id)->order('create_time desc')->limit(1)->find();
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
            ob_flush();   //输出缓冲区的内容   //    必须和下面同时使用 flush()
            flush();  //刷新缓冲区的内容  该函数将当前为止程序的所有输出发送到用户的浏览器,必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲
            $return_data = $callback();
            if ($return_data) {
                return $return_data;
            }
            sleep(1);
        }
        ob_end_flush();  //输出缓冲区内容并关闭缓冲
        return json(['code' => 1, 'data' => (object)array()]);
    }


}
