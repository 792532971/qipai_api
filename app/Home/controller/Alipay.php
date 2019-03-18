<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-12-4
 * Time: 10:51
 */

namespace app\Home\controller;


use app\Home\model\Order;

class Alipay extends Common
{
    public function pay($order_id = '')
    {
        if (!$order = Order::get($order_id)) {
            return ['code' => -1, 'msg' => '订单不存在'];
        }
        if ($order->pay_status == 1) {
            // 订单已支付
            return '订单已支付';
        }
        if (time() - strtotime($order->create_time) > 300) {
            // 订单已过期
            return '订单已过期';
        }
        $end_time = date('Y-m-d H:i:s', strtotime($order->create_time) + 300);
        $url = "alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount=" . $order->amount . "&userId=" . $order->alipay_user_id . "&memo=" . $order->trade_no;
        $this->assign([
            'order_id' => $order->id,
            'trade_no' => $order->trade_no,
            'qrcode' => $url,
            'end_time' => $end_time,
            'amount' => $order->amount,
            'success_url' => $order->success_url
        ]);
        return $this->fetch();
    }

    public function pay_qrcode()
    {

    }

    public function hh()
    {
        return $this->fetch();
    }

}