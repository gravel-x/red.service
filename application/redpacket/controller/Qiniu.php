<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/13
 * Time: 9:50
 */
namespace app\redpacket\controller;

use app\redpacket\service\AudioService;
use app\common\controller\Middle;
use think\Response;

class Qiniu extends Middle
{
    //获取上传音频的token
    public function getUploadToekn()
    {
        $token = AudioService::getUploadToken();
        return Response::create([
            'uptoken'  => $token
        ],'json');
    }

    /*
     * 七牛上传转换回调
     */
    public function upCallback()
    {
        QiniuService::callback();
    }
}