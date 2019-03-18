<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2019-1-24
 * Time: 11:27
 */

namespace app\Home\controller;

use org\wechat\Jssdk;
use think\Config;

class Wechat extends Common
{
    public function index($url = '')
    {
        $jssdkObj = new Jssdk(Config::get('app_id'), Config::get('appsecret'), $url);
        $res = $jssdkObj->getSignPackage();
        $appId = $res['appId'];
        $timestamp = $res['timestamp'];
        $nonceStr = $res['nonceStr'];
        $signature = $res['signature'];
        $data = [
            'appId' => $appId,
            'timestamp' => $timestamp,
            'nonceStr' => $nonceStr,
            'signature' => $signature,
        ];
        return json(['code' => 1, 'data' => $data]);
    }

    public function test()
    {
        return '成功';
    }
}