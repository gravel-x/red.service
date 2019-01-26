<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/17
 * Time: 17:02
 */

namespace app\myswoole\controller;

use app\common\controller\Middle;
use think\Request;
use app\myswoole\service\SwooleService;

class Myswoole extends Middle
{
    public function test()
    {
        $res = SwooleService::test();
        $this->pushSuccess($res);

    }
}
