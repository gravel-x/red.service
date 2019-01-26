<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 9:59
 */

namespace app\redpacket\controller;

use think\Request;
use app\common\controller\Middle;
use app\redpacket\service\CashService;

class Cash extends Middle
{

    public function cashCreate()
    {
        $privately = $this->privately;
        $data = Request::instance()->post();

        $list = CashService::cash_apply($privately,$data);

        $this->pushSuccess($list);
    }
}