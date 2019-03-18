<?php

namespace app\home\controller;

use \app\home\model\ArticleList;


class Article extends Common
{
    /**
     *  请求公告文章
     * @param int $type
     * @return \think\response\Json
     */
    public function listInfo($type = 0)
    {
        header('Access-Control-Allow-Origin:*');
        $list = ArticleList::getList($type);
        return json(['code' => 1, 'data' => $list]);
    }
}
