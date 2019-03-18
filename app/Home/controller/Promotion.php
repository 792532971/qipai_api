<?php
/**
 * Created by PhpStorm.
 * User: TXCMS_V1
 * Date: 2019-1-19
 * Time: 11:49
 */

namespace app\Home\controller;


use core\Token;

/**
 *  推广类
 * Class Promotion
 * @package app\Home\controller
 */
class Promotion extends Common
{
    /**
     *  我的推广
     * @param string $token
     */
    public function myPromition($token = '')
    {
//        $userId = Token::parse($token)['id'];
        $userId = 10002;

    }
}