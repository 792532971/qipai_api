<?php

namespace app\home\model;

use \think\Model;

class ArticleList extends Model
{
    public static function getList($data)
    {
        $list = self::field('img_url as content');
        if ($data) {
            $list->where('cate_id', $data);
        }
        return $list->limit(1)->order('create_time desc')->select();
    }
}