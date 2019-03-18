<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-12-6
 * Time: 9:37
 */

namespace app\api\controller;

use app\api\model\OrderLog;
use app\home\model\User;
use core\Token;
use think\Controller;
use think\Db;

class Order extends Controller
{
    private $key_id = 'bb17fbb7c1393e2fd2157bbd4be29760';
    private $account_id = 1000000;

    /**
     *  充值
     * @param string $token
     * @param int $amount
     * @return mixed|\think\response\Json
     */
    public function pay($token = '', $amount = 0)
    {
        //获取token
        //最少0.01 最高10000;
        if ($amount < 0.01 || $amount > 10000) {
            return json(['code' => 0, 'msg' => '金额最低0.01,最高10000']);
        }
        $token = Token::parse($token)['id'];
        if (!$token) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        // 订单号
        $trade_no = date("YmdHis") . mt_rand(10000, 99999);
        $callback_url = 'http://192.168.4.109:8888/api/order/callback';
        $success_url = request()->domain() . url('suc');
        $error_url = request()->domain() . url('err');
        // 签名
        $sign = sign($this->key_id, [
            'amount' => $amount,
            'trade_no' => $trade_no
        ]);
        $OrderLog = new OrderLog();
        $OrderLog->save(['trade_no' => $trade_no, 'user_id' => $token, 'create_time' => now()]);
        $this->assign([
            'account_id' => $this->account_id,
            'amount' => $amount,
            'trade_no' => $trade_no,
            'callback_url' => $callback_url,
            'success_url' => $success_url,
            'error_url' => $error_url,
            'sign' => $sign
        ]);
        return $this->fetch();
    }


    /**
     *  回调页面
     * @param int $amount
     * @param string $order_id
     * @param string $trade_no
     * @param string $sign
     * @return \think\response\Json
     */
    public function callback($amount = 0, $order_id = '', $trade_no = '', $sign = '')
    {
        $OrderLog = new \app\api\model\OrderLog();
        $User = new User();
        $sys_sign = sign($this->key_id, [
            'amount' => $amount,
            'order_id' => $order_id,
            'trade_no' => $trade_no
        ]);
        if ($sys_sign != $sign) {
            return json(['code' => 0, 'msg' => '验签不通过']);
        }
        $userId = $OrderLog->where('trade_no', $trade_no)->find()->user_id;
        $before_coin = $User->where('id', $userId)->find()['coin'];
        $after_coin = $before_coin + $amount;
        $data = [
            'user_id' => $userId,
            'action_id' => 1,
            'coin' => $amount,
            'before_coin' => $before_coin,
            'after_coin' => $after_coin,
            'info' => '充值',
            'create_time' => now(),
            'create_ip' => request()->ip(),
        ];
        Db::table('detail_log')->insert($data);
        $User->where('id', $userId)->setInc('coin', $amount);

    }

    /**
     *  支付成功跳转页面
     * @return \think\response\Json
     */
    public function suc()
    {
        return json(['code' => 1, 'msg' => '支付成功']);
    }

    /**
     *  支付失败跳转页面
     * @return \think\response\Json
     */
    public function err()
    {
        return json(['code' => 0, 'msg' => '支付失败']);
    }


}