<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/7
 * Time: 14:56
 */

namespace app\redpacket\controller;

use app\redpacket\service\AudioService;
use app\common\controller\Middle;
use think\Request;
use app\redpacket\service\QiniuService;
use think\Response;

class Audio extends Middle
{
    //语音识别
    public function upAudio()
    {
        $data = request()->file('voice');
        $isMove = $data->move(ROOT_PATH . 'public' . DS . 'uploads');

        if ($isMove) {
            //获取上传后的音频路径
            $moveUrl = $isMove->getSaveName();
            $privately = $this->privately;
            $data = Request::instance()->post();
            $data['voice'] = $moveUrl;
            $res = QiniuService::speechNew($data,$privately);

            return $res;
        }else{
            Response::create([
                'status' => false,
                'code'   => 4101,
                'data'   => '上传音频失败！'
            ],'json')->send();
        }
    }

}