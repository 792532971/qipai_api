<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:25
 */

namespace app\home\controller;

use app\home\model\Bank as BankModel;
use core\RedisDb;
use core\Token;
use think\cache\driver\Redis;

class Bank extends Common
{

    /**
     *  请求银行余额
     * @param string $token
     * @return \think\response\Json
     */
    public function coin(string $token = '')
    {
        $Bank = new BankModel();
        $token = Token::parse($token)['id'];
        $coin = $Bank->where('user_id', $token)->sum('coin');
        return json(['code' => 1, 'data' => $coin]);
    }

    /**
     *  银行存取操作
     * @param string $token
     * @param int $type
     * @param int $coin
     * @return \think\response\Json
     */
    public function inCoin(string $token = '', int $type = 0, int $coin = 0)
    {
        $Bank = new BankModel();
        $user_id = Token::parse($token)['id'];
        $data = $Bank->getCoins($user_id, $type, $coin);
        if ($data === 1) {
            $bank_balance = $Bank->where('user_id', $user_id)->find()['coin'];
            //银行余额  ,用户余额
            $user_balance = db('user')->where('id', $user_id)->find()['coin'];
            return json(['bank_coin' => $bank_balance, 'coin' => $user_balance]);
        } elseif ($data === 3) {
            $bank_balance = $Bank->where('user_id', $user_id)->find()['coin'];
            //银行余额  ,用户余额
            $user_balance = db('user')->where('id', $user_id)->find()['coin'];
            return json(['bank_coin' => $bank_balance, 'coin' => $user_balance]);
        } elseif ($data === 4) {
            return json(['code' => 0, 'msg' => '取款失败']);
        } elseif ($data === 0) {
            return json(['code' => 1, 'msg' => '存款失败']);
        } elseif ($data === 5) {
            return json(['code' => 0, 'msg' => '银行余额不足']);
        } elseif ($data === 7) {
            return json(['code' => 0, 'msg' => '余额不足无法充值']);
        } elseif ($data === 8) {
            return json(['code' => 0, 'msg' => '存款不足']);
        }

    }

}