<?php
/**

 * Created by PhpStorm.

 * User: Administrator
 * Date: 2018/4/13
 * Time: 13:41
 */
namespace app\message\controller;
use app\common\controller\Middle;
use app\message\service\PresetService;

class Preset extends Middle
{

    //预设口令展示
    public function preset()
    {
         $res = PresetService::getPreset();
         $this->pushSuccess($res);
    }

    //弹幕列表
    public function barrage()
    {
        $res = PresetService::barrage();
        $this->pushSuccess($res);
    }

    //修改弹幕
    public function update_barrage()
    {
        $info = input('get.');
        $res = PresetService::update_barrage($info);
        $this->pushSuccess($res);
    }

    //弹幕开关
    public function is_barrage()
    {
        $info = input('get.');
        $res = PresetService::is_barrage($info);
        $this->pushSuccess($res);
    }

    //比率
    public function getProportion()
    {
        $list = PresetService::getProportion();
        $this->pushSuccess($list);
    }


    //模板消息
    public function template()
    {
        $uid = $this->privately['user_id'];
        $info = input('get.');
        $res = PresetService::template($info,$uid);
        $this->pushSuccess($res);
    }


}