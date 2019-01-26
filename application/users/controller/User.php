<?php
/**
 * User: greatsir
 * Date: 2018/4/12
 * Time: 10:26
 */
namespace app\users\controller;
use app\common\controller\Middle;
use app\users\service\UserService;
use think\Request;

class User extends Middle
{
    public function ac_token()
    {
        $record = UserService::ac_token();
        $this->pushSuccess($record);
    }

    //我的记录  收到和发出头部
    public function record()
    {
        $data   = input('get.');
        $uid    = $this->privately['user_id'];
        $record = UserService::record($data,$uid);
        $this->pushSuccess($record);
    }


    //我的记录  收到和发出底部
    public function record_list()
    {
        $data   = input('get.');
        $uid    = $this->privately['user_id'];
        $record = UserService::record_list($data,$uid);
        $this->pushSuccess($record);
    }

    //红包详情头部（点击记录后）
    public function red_details()
    {
        $uid  = $this->privately['user_id'];
        $data = input('get.');
        $details = UserService::red_details($data,$uid);
        $this->pushSuccess($details);
    }

    //红包详情记录列表
    public function details_list()
    {
        $data = input('get.');
        $details_list = UserService::details_list($data);
        $this->pushSuccess($details_list);
    }

    /*
     * 获取我的语音
     */
    public function getMyVoice()
    {
        $data = input('get.');
        $uid  = $this->privately['user_id'];
        $list = UserService::getMyVoice($data,$uid);
        $this->pushSuccess($list);
    }

    public function QR_code()
    {
        $red = input('post.');
        $res_id = $red['red_id'];
        $data = UserService::QR_code($res_id);  //原QR_code
        $this->pushSuccess($data);
    }

    public function adQr()
    {
        $data = input('post.');
        $res  = UserService::AD_QR($data['red_id']);
        $this->pushSuccess($res);
    }


    //举报
    public function report($content)
    {
        $uid = $this->privately['user_id'];
        $result = UserService::report($content,$uid);
        $this->pushSuccess($result);
    }

    //后台举报查看列表
    public function report_list($page = null,$search = null)
    {
        $result = UserService::report_list($page,$search);
        $this->pushSuccess($result);
    }


    //查看被举报红包详情
    public function report_detail($red_id)
    {
        $result = UserService::report_detail($red_id);
        $this->pushSuccess($result);
    }


    //二维码后台查看
    public function adQr_list($page)
    {
        $result = UserService::adQr_list($page);
         $this->pushSuccess($result);
    }

    //二维码后台增加，修改
    public function adQr_set($data)
    {
        $result = UserService::adQr_set($data);
        $this->pushSuccess($result);
    }


    //二维码后台删除
    public function adQr_del($id)
    {
        $result = UserService::adQr_del($id);
        $this->pushSuccess($result);

    }

    //增加分享次数
    public function addShareTimes()
    {
        $data = input('get.');
        $privately = $this->privately;
        $result = UserService::addShareTimes($privately,$data);
        $this->pushSuccess($result);

    }
}