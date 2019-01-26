<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/3
 * Time: 10:01
 */

namespace app\common\service;

use think\Controller;
use think\Response;

class PushService extends Controller
{
    //推送接口调用失败消息,错误信息直接推送给前端
    public static function pushError($code,$error)
    {
         Response::create([
            'status' => false,
            'code'   => $code,
            'data'=> $error
        ],'json')->send();
         die;
    }
}