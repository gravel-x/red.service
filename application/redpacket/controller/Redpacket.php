<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/11
 * Time: 17:35
 */
namespace app\redpacket\controller;

use app\common\controller\Middle;
use think\Request;
use app\redpacket\service\RedpacketService;
use Payment\Client\Notify;
use Payment\Common\PayException;
use app\common\service\TestNotify;

class Redpacket extends Middle
{
    public function create()
    {
        $privately = $this->privately;
        $data = Request::instance()->get();
        $res = RedpacketService::create_redpacket($privately,$data);
        $this->pushSuccess($res);
    }

    public function pay_success()
    {
        $wxConfig = config('wxpay');
        $callback = new TestNotify();
        $config = $wxConfig;
        try {
            //$retData = Notify::getNotifyData($type, $config);// 获取第三方的原始数据，未进行签名检查
            $ret = Notify::run('wx_charge', $config, $callback);// 处理回调，内部进行了签名检查
            return $ret;
        } catch (PayException $e) {
            return $e->errorMessage();
            exit;
        }
    }

    public function getRedInfo($id)
    {
        $uid = $this->privately['user_id']??'';
        $redinfo = RedpacketService::read($id,$uid);
        $this->pushSuccess($redinfo);
    }

    public function adRedpacket()
    {
        $res = RedpacketService::adRedPacket();
        $this->pushSuccess($res);
    }

    //微信自带语音识别
    public function recognise()
    {
        $data = Request::instance()->get();
        $privately = $this->privately;
        $res = RedpacketService::recognise($privately,$data);
        $this->pushSuccess($res);
    }

    public function voice_url()
    {
        $data = Request::instance()->get();
        $privately = $this->privately;
        $res = RedpacketService::voice_url($privately,$data);
        $this->pushSuccess($res);
    }

    public function listen()
    {
        $data = Request::instance()->get();
        $privately = $this->privately;
        $res = RedpacketService::listen($privately,$data);
        $this->pushSuccess($res);
    }
}