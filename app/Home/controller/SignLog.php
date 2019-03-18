<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-12-31
 * Time: 14:43
 */

namespace app\Home\controller;

use app\Home\model\SignLog as SignLogModel;
use app\Home\model\User as UserModel;
use core\Token;
use think\helper\Time;

class SignLog extends Common
{
    /**
     *  签到列表
     * @param string $token
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function signList($token = '')
    {
        $user_id = 10003;
//        $user_id = Token::parse($token)['id'];
        if (!($user_id)) {
            return json(['code' => 0, 'msg' => '缺少参数']);
        }
        $list = db('sign_prize')->field('id,day,reward,icon,type,name,des')->select();
        $isOk = db('sign_log')->where('user_id', $user_id)->order('id desc')->find();
        $last_time = strtotime($isOk['sign_time']);//最后一次签到时间
        $t = time();
        // $start = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));
        $end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));//当天时间的最后1刻 (注意)
        $current_day = $end;//当天时间的最后1刻
        $timeIs = $this->timeDiff($last_time, $current_day);//时间差
        if (!empty($isOk)) {
            foreach ($list as $key => $value) {
                //判断是否漏签
                if ($timeIs['day'] > 1) {// 漏签
                    if ($value['day'] == 1) {
                        $list[$key]['is_sign'] = 1;//仅第一天的可以签 即首次签到
                    } else {
                        $list[$key]['is_sign'] = 2;//不可以签
                    }
                } else {// 未漏签
                    if ($isOk['sign_num'] == 7) {//签满7天后
                        if ($timeIs['day'] == 1) {//今天没签
                            if ($value['day'] == 1) {
                                $is_sign = 1;//仅第一天的可以签 即首次签到
                            } else {
                                $is_sign = 2;//不可以签
                            }
                        } elseif ($timeIs['day'] == 0) {//今天签过
                            $is_sign = 3;//已签到过
                        }

                    } else {//未签满7天，如1-6天之间

                        if ($timeIs['day'] == 1) {//今天没签
                            if ($value['day'] == $isOk['sign_num'] + 1) {
                                $is_sign = 1;//可以签
                            } elseif ($value['day'] <= $isOk['sign_num']) {
                                $is_sign = 3;//已签到过
                            } else {
                                $is_sign = 2;//不可以签
                            }
                        } elseif ($timeIs['day'] == 0) {//今天签过

                            if ($value['day'] <= $isOk['sign_num']) {
                                $is_sign = 3;//已签到过
                            } else {
                                $is_sign = 2;//不可以签
                            }
                        }
                    }
                    $list[$key]['is_sign'] = $is_sign;
                }
            }
        } else {//首次签到
            foreach ($list as $k => $v) {
                if ($v['day'] == 1) {
                    $list[$k]['is_sign'] = 1;//仅第一天的可以签 即首次签到
                } else {
                    $list[$k]['is_sign'] = 2;//不可以签
                }
            }
        }
        $re['data'] = $list;
        $re['message'] = '请求成功';
        return json(['code' => 1, 'data' => $re]);
    }

    /**
     *  计算两个时间戳之间相差的日时分秒
     * @param $beginTime
     * @param $endTime
     * @return array
     */
    public function timeDiff($beginTime, $endTime)
    {

        if ($beginTime < $endTime) {
            $startTime = $beginTime;
            $end_time = $endTime;
        } else {
            $startTime = $endTime;
            $end_time = $beginTime;
        }

        //计算天数
        $timeDiff = $end_time - $startTime;
        $days = intval($timeDiff / 86400);
        //计算小时数
        $remain = $timeDiff % 86400;
        $hours = intval($remain / 3600);
        //计算分钟数
        $remain = $remain % 3600;
        $min_s = intval($remain / 60);
        //计算秒数
        $secs = $remain % 60;
        $res = ["day" => $days, "hour" => $hours, "min" => $min_s, "sec" => $secs];
        return $res;
    }

    public function startSign($token = '')
    {
//        $userId = Token::parse($token)['id'];
        $userId = 10003;
        $SignLog = new SignLogModel();
        if (!is_null($row = $SignLog->where('user_id', $userId)->find())) {  //该用户存在就签到跟新记录表状态
            $last_sign_time = strtotime($row->sign_time);
            $sign_day = $row->sign_num;
            if ($sign_day === 7) {

            }
            //根据上次签到时间和这次签到时间作比较判断有没有漏签和今日是否已签到
            $current_total_day = intval($sign_day) + 1;
            $current_time = time();
            $ary = $this->timeDiff($last_sign_time, $current_time);
            if ($ary['day'] == 0) { //今天已签到
                return json(['code' => 0, 'msg' => '今天已经签到']);
            } elseif ($ary['day'] == 1) { //没有漏签
                $res = db('sign_log')->where('user_id', $userId)->update(['sign_time' => date('Y-m-d H:i:s', $current_time), 'sign_num' => $current_total_day]);
                if ($res) {
                    $this->upuserscore($current_total_day, $userId);
                    return json(['code' => 1, 'msg' => '签到成功1']);
                } else {
                    return json(['code' => 1, 'msg' => '成功']);
                }
            } else { //漏签过
                $re = db('sign_log')->where('user_id', $userId)->update(['sign_time' => date('Y-m-d H:i:s', $current_time)]);
                if ($re) {
                    $this->upuserscore($current_total_day, $userId);
                    return json(['code' => 1, 'msg' => '签到成功3']);
                } else {
                    return json(['code' => 1, 'msg' => '成功']);
                }
            }
        } else { //没有该用户记录则插入记录
            $data = [
                'user_id' => $userId,
                'sign_time' => now(),
                'sign_num' => 1
            ];
            $SignLog->insert($data);
        }

    }

    public function upuserscore($current_total_day, $userId)
    {
        $User = new UserModel();
        $total_exp = self::getScore($current_total_day);
        $User->where('id', $userId)->setInc('vip_exp', $total_exp);
    }

    /**
     *  获取积分
     * @param $day
     * @return int
     */
    protected static function getScore($day)
    {
        switch ($day) {
            case 1:
                $exp = 5;
                break;
            case 2:
                $exp = 8;
                break;
            case 3:
                $exp = 11;
                break;
            case 4:
                $exp = 15;
                break;
            case 5:
                $exp = 19;
                break;
            case 6:
                $exp = 24;
                break;
            case 7:
                $exp = 29;
                break;
            default:
                $exp = 29;
        }
        return $exp;
    }

    /**
     *  签到接口
     * @param string $token
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function signBtn($token = '')
    {
        $id = Token::parse($token)['id'];
        $day = date('w');
        if (!$data = db('sign_log')->where('user_id', $id)->find()) {
            if ($day == 0) {
                $day = 7;
            }
            $data1 = [0, 0, 0, 0, 0, 0, 0];
            $data1[$day - 1] = 1;
            $res = json_encode($data1, true);
            db('sign_log')->where('user_id', $id)->insert(['user_id' => $id, 'sign_time' => now(), 'sign_val' => $res, 'sign_num' => 1]);
        } else {
            if ($day == 0) {
                $day = 7;
            }
            if ($day == 1) {
                $data['sign_val'] = json_encode([1, 0, 0, 0, 0, 0], true);
                db('sign_log')->where('user_id', $id)->update(['sign_val' => $data]);
            }
            $last_time = strtotime($data['sign_time']);
            $dayy = $this->timediff($last_time, time());
            if ($dayy['day'] == 0) {  // 已签
                return json(['code' => 0, 'msg' => '今天已签到']);
            } elseif ($dayy['day'] == 1) {
                //没有漏签
                $sign_num = $data['sign_num'];
                if ($sign_num != 7) {
                    $data_q = json_decode($data['sign_val']);
                    $data_q[$day] = 1;
                    $data['sign_val'] = json_encode($data_q, true);
                    $data['sign_time'] = now();
                    $data['sign_num'] += 1;
                    $this->upPrice($data['sign_num']);
                    db('sign_log')->where('user_id', $id)->update($data);
                    return json(['code' => 1, 'msg' => '签到成功']);
                } else {
                    $data_q = json_decode($data['sign_val']);
                    $data_q[$day] = 1;
                    $data['sign_val'] = json_encode($data_q, true);
                    $data['sign_time'] = now();
                    unset($data['sign_num']);
                    $data['sign_num'] = 1;
                    $this->upPrice($data['sign_num']);
                    //算积分
                    db('sign_log')->where('user_id', $id)->update($data);
                    return json(['code' => 1, 'msg' => '签到成功']);
                }
            } else {  //漏签
                db('sign_log')->where('user_id', $id)->update(['sign_time' => now(), 'sign_num' => 1]);
                return json(['code' => 1, 'msg' => '签到成功']);
            }

        }

    }

    /**
     * @param string $token
     * @return \think\response\Json
     */
    public function signIndex($token = '')
    {
        $userId = Token::parse($token);
        $data = db('sign_log')->where('user_id', $userId)->field('sign_val')->find()['sign_val'];
        return json(['code' => 1, 'data' => $data]);

    }

    /**
     *  奖品发放功能
     * @param $num
     */
    public function upPrice($num)
    {

    }

}