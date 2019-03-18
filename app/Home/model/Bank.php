<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 11:26
 */

namespace app\home\model;


use core\Token;
use think\cache\driver\Redis;
use think\Model;

class Bank extends Model
{
    /**
     *  用户存取款操作
     * @param $user_id
     * @param $type
     * @param $coin
     * @return int
     */
    public static function getCoins($user_id, $type, $coin)
    {
        $detail_log = new DetailLog();
        $Bank = new Bank();
        $User = new User();

        $ree = $Bank->where('user_id', $user_id)->find();
        $rees = $User->where('id', $user_id)->find();
        if ($ree === null) {
            $data = [
                'user_id' => $user_id,
                'coin' => 0,
            ];
            $Bank->insert($data);
        }
        $before_bank_coin = $ree['coin'];   //之前的银行余额
        $before_user_coin = $rees['coin'];    //之前的用户余额

        //存款
        if ($type == 1) {
            $self_coin = $User->where('id', $user_id)->find();   //找出自己身上得钱
            //如果过存款金额大于自己身上的金额就报错误
            if ($self_coin['coin'] < $coin) {
                return 7;  //余额不足无法充值;
            }
            $User->where('id', $user_id)->setDec('coin', $coin);
            $resultt = $Bank->where('user_id', $user_id)->setInc('coin', $coin);
            if ($resultt == 1) {
                $bank_coin = $Bank->where('user_id', $user_id)->find()['coin']; //现在的银行余额
                $user_coin = $User->where('id', $user_id)->find()['coin']; //现在的自己余额
                $data = [
                    'user_id' => $user_id,
                    'action_id' => 4,
                    'coin' => $coin,
                    'before_coin' => $before_bank_coin + $before_user_coin,
                    'after_coin' => $bank_coin + $user_coin,
                    'info' => '银行存款',
                    'create_time' => date('Y-m-d H:i:s', time()),
                    'create_ip' => request()->ip()
                ];
                $detail_log->insert($data);
                return 1;
            } else {
                return 0;
            }
        }
        //取款
        $res = $Bank->where('user_id', $user_id)->find();
        if ($coin > $res['coin']) {
            return 8;
        }
        $res->coin -= $coin;
        $res->save();
        $self_user = User::get($user_id);
        $self_user->coin += $coin;
        $ress = $self_user->save();

        if ($ress == 1) {
            $bank_coin2 = $Bank->where('user_id', $user_id)->find()['coin']; //现在的银行余额
            $user_coin2 = $User->where('id', $user_id)->find()['coin']; //现在的自己余额
            $data = [
                'user_id' => $user_id,
                'action_id' => 5,
                'coin' => $coin,
                'before_coin' => $before_bank_coin + $before_user_coin,
                'after_coin' => $bank_coin2 + $user_coin2,
                'info' => '银行取款',
                'create_time' => date('Y-m-d H:i:s', time()),
                'create_ip' => request()->ip()
            ];
            $detail_log->insert($data);
            return 3;
        } else {
            return 4;
        }


    }
}