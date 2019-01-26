<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 17:29
 */

namespace app\user\controller;
use app\common\controller\Middle;
use app\user\service\UserService;
use think\Request;



class User extends Middle
{
    //解密code 获得用户openid和key
    public function decrypt(){
        $privately = $this->privately;
        $data = Request::instance()->post();

        $res = UserService::decrypt($privately,$data);
        $this->pushSuccess($res);
    }

//    获取accessToken
    public function getAccessToken()
    {
        $data = Request::instance()->post();
        $res = UsersService::getAccessToken($data);
    }

    //更新用户信息
    public function upUserInfo(){
        $privately = $this->privately;
        $data = Request::instance()->post();
        $res = UserService::upUserInfo($privately,$data['userInfo']);
        $this->pushSuccess($res);
    }


    public function getUserInfo()
    {
        $privately = $this->privately;
        $res = UserService::getUserInfo($privately['user_id']);
        $this->pushSuccess($res);
    }




}