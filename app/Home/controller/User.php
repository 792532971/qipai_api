<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2018-9-21
 * Time: 10:51
 */

namespace app\home\controller;

use app\Home\model\Level;
use app\Home\model\LotteryLog;
use app\home\model\User as UserM;
use app\home\model\DetailLog;
use core\Token;
use GatewayClient\Gateway;
use think\helper\Time;

class User extends Common
{

    /**
     * 请求赢家排行
     * @param string $token
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function rankWin($token = '')
    {
        $userId = Token::parse($token)['id'];
        $DetailLog = new DetailLog();
        $data = $DetailLog->query("select d.user_id,sum(d.coin) coin,d.action_id,u.nickname from `detail_log` as d join `user` as u on d.user_id=u.id  GROUP BY user_id having coin >0 and action_id=2 ORDER BY coin desc;");
        $user_s = [];
        foreach ($data as $v) {
            $user_s[] = $v['user_id'];
        }
        $ress = array_search($userId, $user_s);
        $paiming = $ress + 1;
        foreach ($data as &$d) {
            unset($d['user_id']);
        }
        $row = [
            'data' => $data,
            'self' => [
                'paiming' => $paiming,
                'coin' => $data[$paiming - 1]['coin'],
                'nickname' => $data[$paiming - 1]['nickname'],
            ]
        ];
        return json(['code' => 1, 'data' => $row]);
    }

    /**
     *  请求等级排行
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function rankLevel()
    {
        $User = new UserM();
        $data = $User->getUserLevel();
        return json(['code' => 1, 'data' => $data]);
    }

    /**
     * 幸运榜
     * @param string $token
     * @return \think\response\Json
     * @throws \think\db\exception\BindParamException
     * @throws \think\exception\PDOException
     */
    public function rankLucky($token = '')
    {
        $userId = Token::parse($token)['id'];
        $DetailLog = new DetailLog();
        $data = $DetailLog->query("select d.user_id,sum(d.coin) coin,d.action_id,u.nickname,DATE_FORMAT(d.create_time, '%H:%i') as `time` from `detail_log` as d join `user` as u on d.user_id=u.id  GROUP BY user_id having coin >0 and action_id=2 ORDER BY coin desc;");
        $user_s = [];
        foreach ($data as $v) {
            $user_s[] = $v['user_id'];
        }
        $ress = array_search($userId, $user_s);
        $paiming = $ress + 1;

        foreach ($data as &$d) {
            unset($d['user_id']);
        }
        $row = [
            'data' => $data,
            'self' => [
                'paiming' => $paiming,
                'coin' => $data[$paiming - 1]['coin'],
                'nickname' => $data[$paiming - 1]['nickname'],
                'time' => $data[$paiming - 1]['time'],
                'action_id' => $data[$paiming - 1]['action_id']
            ]
        ];
        return json(['code' => 1, 'data' => $row]);
    }

    /**
     * 修改用户昵称
     * @param string $token
     * @param string $nickname
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setName($token = '', $nickname = '')
    {
        $userId = Token::parse($token)['id'];
        $result = $this->validate(
            [
                'nickname' => trim($nickname),
            ],
            [
                'nickname' => 'require|min:2|max:8',
            ]);
        if (true !== $result) {
            // 验证失败 输出错误信息
            return json(['code' => 0, 'msg' => $result]);
        }
        $user = db('user')->where('id', $userId)->update(['nickname' => trim($nickname)]);
        if ($user) {
            return json(['code' => 1, 'nickname' => $nickname]);
        } else {
            return json(['code' => 0, 'msg' => '修改失败']);
        }
    }

    /**
     * 修改用户头像
     * @param string $token
     * @param int $photo
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setHead($token = '', $photo = 0)
    {
        $userId = Token::parse($token)['id'];
        $user = db('user')->where('id', $userId)->update(['photo' => $photo]);
        if ($user) {
            return json(['code' => 1, 'photo' => $photo]);
        } else {
            return json(['code' => 0, 'msg' => '修改失败']);
        }
    }


    /**
     * 给客户端发送信息
     * @param string $token
     * @throws \think\exception\DbException
     */
    public function user_info($token = '')
    {
        $userId = Token::parse($token)['id'];
        $data = UserM::get($userId);
        Gateway::$registerAddress = '192.168.4.146:1238';
        $res = [
            'id' => $data->id,
            'user_id' => $userId,
            'coin' => $data->coin,
            'nickname' => $data->nickname,
            'photo' => $data->photo
        ];
        $data = parseSocketData('user_info', $res);
        Gateway::sendToUid($userId, $data);
    }

    /**
     * 会员等级获取判定
     * @param string $token
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function userExpLevel($token = '')
    {
        $User = new UserM();
        $userId = Token::parse($token)['id'];
        $vip_exp = $User->where('id', $userId)->field('vip_exp')->find()->vip_exp;  //获取当前经验值
        $Level = new Level();
        $min = $Level->min('exp');
        $max = $Level->max('exp');
        if ($vip_exp > $max) {
            $data = [
                'level' => '至尊无上',
                'dis' => '100%'
            ];
            return json(['code' => 1, 'data' => $data]);
        }
        if ($vip_exp < $min) {
            $data = [
                'level' => '包身工',
                'dis' => UserM::dis($vip_exp, $min)
            ];
            return json(['code' => 1, 'data' => $data]);
        }
        $res = $Level->where('exp', '>', $vip_exp)->find()->exp;
        $row = $Level->where('exp', '<', $res)->order('exp desc')->find();
        $data = [
            'level' => $row->level_name,
            'dis' => UserM::dis($vip_exp, $res)
        ];
        return json(['code' => 1, 'data' => $data]);
    }


    /**
     * 转盘抽奖
     * @param string $token
     * @param int $type
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function turntable(string $token = '', int $type = 0)
    {
        if ($type == 0) {
            return json(['code' => 0, 'msg' => '操作有误']);
        }
        $req_exp = db('lottery_cate')->where('id', $type)->field('req_exp,status')->find();
        if ($req_exp['status'] === 0) {
            return json(['code' => 0, 'msg' => '此轮盘维护中']);
        }
        $User = new UserM();
        $userId = Token::parse($token)['id'];
        $user = $User->where('id', $userId)->field('vip_exp,nickname,coin,photo')->find();
        $vip_exp = $user->vip_exp;
        if ($vip_exp < $req_exp['req_exp']) {
            return json(['code' => 0, 'msg' => '当前积分小于' . $req_exp['req_exp'] . '无法抽奖']);
        }
        $User->where('id', $userId)->setDec('vip_exp', $req_exp['req_exp']);
        $db = '';
        if ($type == 1) {
            $db = 'lottery';
        } elseif ($type == 2) {
            $db = 'lottery_middle';
        } elseif ($type == 3) {
            $db = 'lottery_high';
        }
        $prize_arr = db($db)->select();
        $arr = [];
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['pro'];//概率数组
        }
        $rid = self::get_rand($arr); //根据概率获取奖项id
        $res['yes'] = $prize_arr[$rid - 1]['prize']; //中奖项
        $res['id'] = $prize_arr[$rid - 1]['id']; //中奖项id
        $res['num'] = $prize_arr[$rid - 1]['num']; //中奖金额
        unset($prize_arr[$rid - 1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        $pr = [];
        for ($i = 0; $i < count($prize_arr); $i++) {
            $pr[] = $prize_arr[$i]['prize']; //未中奖项数组
        }
        $vip_exp2 = $User->where('id', $userId)->value('vip_exp');
        $result['prize'] = (int)$res['yes'];
        $result['num'] = $res['num'];   //获奖金币
        $result['vip_exp'] = $vip_exp2;     //当前剩余经验
        //----------------------------------
        //写入抽奖日志表
        $data = [
            'user_id' => $userId,   //抽奖人
            'prize' => $result['prize'],  //抽中结果
            'create_time' => now(),        //抽奖时间//
            'create_ip' => request()->ip(),
            'type' => $type,
            'exp' => -$req_exp['req_exp'],
            'num' => $res['num'],
            'before_exp' => $vip_exp,    //操作前经验
            'after_exp' => $vip_exp2      //操作后经验
        ];
        db('lottery_log')->insert($data);
        // --------------------------------
        //写入资金明细表
        $data2 = [
            'user_id' => $userId,
            'action_id' => 6,  //游戏抽奖
            'coin' => $res['num'],
            'before_coin' => $user->coin,   //抽奖前金币
            'after_coin' => $user->coin + $res['num'],      //抽奖后金币
            'info' => '游戏抽奖',
            'create_time' => now(),
            'create_ip' => request()->ip()
        ];
        $user->where('id', $userId)->setInc('coin', $res['num']);
        db('detail_log')->insert($data2);
        /*返回值
        获奖值:
        抽中金币: num
        当前剩余经验  vip_exp
        */
        return json(['code' => 1, 'data' => $result]);
    }

    //计算中奖概率
    private static function get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum); //返回随机整数
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }

    /**
     *  抽奖排行
     * @param string $token
     * @return \think\response\Json
     */
    public function lotteryRanking($token = '')
    {
        $userId = Token::parse($token)['id'];
        list($start, $end) = Time::today();
        //抽奖时间
        $data = db('lottery_log')->field('create_time,user_id as nickname,type,num')->whereTime('create_time', 'between', [$start, $end])->order('num desc')->select();
        foreach ($data as $k => &$v) {
            $v['nickname'] = db('user')->where('id', $v['nickname'])->value('nickname');
            $v['create_time'] = date('H:i', strtotime($v['create_time']));
        }
        //d当前剩余积分
        $last_exp = db('user')->where('id', $userId)->value('vip_exp');
        $data_s = [
            'last_exp' => $last_exp,
            'today_exp' => 8888,  //TODO:今天消耗的经验
            'data' => $data
        ];
        return json(['code' => 1, 'data' => $data_s]);

    }


    /**
     * 轮询获取数据
     * @param int $id
     * @return \think\response\Json
     */
    public function lucky_news($id = 0)
    {
        if ($id == 0) {
            $res = db('lottery_log')->field('id,type,user_id,num,prize_name')->order('id desc')->limit(20)->select();
            foreach ($res as &$v) {
                $v['nickname'] = db('user')->where('id', $v['user_id'])->value('nickname');
                unset($v['user_id']);
            }
            return json(['code' => 1, 'data' => $res]);
        }

        return  longPolling(function () use ($id) {
            $data = db('lottery_log')->field('id,num,prize_name,user_id,type')->where('id', '>', $id)->select();
            foreach ($data as &$v) {
                $v['nickname'] = db('user')->where('id', $v['user_id'])->value('nickname');
                unset($v['user_id']);
            }
            return json(['code' => 1, 'data' => $data ?? (object)[]]);
        });
    }


    /**
     *  签到列表
     * @param string $token //token参数必须
     * @return \think\response\Json  //返回json数据
     */
    public function signList($token = '')
    {
        $user_id = Token::parse($token)['id'];
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


}