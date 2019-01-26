<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/6
 * Time: 下午3:07
 */

namespace app\users\controller;

use app\users\service\AccessService;
use think\Controller;

class Access extends Controller
{
    /*
     *  返回token
     */
    public function getAccessToken()
    {
        $userInfo = AccessService::getAccessToken();
    }
}