<?php

/** Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 15:12
 */
namespace app\detail\controller;
use app\common\controller\Middle;
use app\detail\service\DetailService;
use think\Request;
class Detail extends Middle
{

    //发出 红包明细
    public function sendDetail()
    {
        $uid = $this->privately['user_id'];
        $res = DetailService::sendDetail($uid);
        $this->pushSuccess($res);

    }

    //收取 红包明细
    public function receiveDetail()
    {
        $uid = $this->privately['user_id'];
        $res = DetailService::receiveDetail($uid);
        $this->pushSuccess($res);

    }

    //提现明细
    public function cashDetail()
    {
        $uid = $this->privately['user_id'];
        $res = DetailService::cashDetail($uid);
        $this->pushSuccess($res);

    }


    //退款明细
    public function refund()
    {
        $privately = $this->privately;
        $res = DetailService::refund($privately['user_id']);
        $this->pushSuccess($res);

    }

    //已发红包列表
    public function send_red_list()
    {
        $page = Request::instance()->post();
        $res = DetailService::send_red_list($page);
        return $this->pushSuccess($res);

    }

    //已收红包列表
    public function received_red_list()
    {
        $page = Request::instance()->post();
        $res = DetailService::received_red_list($page);
        return $this->pushSuccess($res);

    }
}
