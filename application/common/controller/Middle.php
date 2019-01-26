<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/21
 * Time: 10:08
 */

namespace app\common\controller;

use app\common\service\auth\AuthService;
use Firebase\JWT\JWT;
use think\Controller;
use think\Request;
use think\Response;
use anu\SingleFactory;
use app\common\service\AppService;


class Middle extends Controller
{
    public $privately = array();
    public function __construct(Request $request = null)
    {
        /**
         *  调取接口头部是否存在identity
         *  identity 传入的token值
         */
        parent::__construct($request);
        $header = $request->header();

        //存在就写，不存在就不管
        if (isset($header['token'])) {

            $identity = $header['token'];
            $this->privately['user_id'] = $this->checkToken($identity);
        }
        if (isset($header['edition'])) {
            $Edition  = $header['edition'];
            $res = AppService::getAppInfo($Edition);
            $this->privately['projectId'] = $Edition;
            $this->privately['appId']   = $res['app_aid'];
            $this->privately['secret']  = $res['app_secret'];
        }

        SingleFactory::overAllData('privately',$this->privately);
    }

    /**
     *  登录验证    过期和错误
     *  @$identity come from http-header
     */
    public function checkToken($identity)
    {

        $key = config('jwt-key');  //解密密钥
        $payload = JWT::decode($identity,$key,array('HS256'));  //解密函数

        if (time()>$payload->exp) {
            Response::create([
                'status' => 'failed',
                'code'    => 1002,
                'data'   =>  'identity 过期'

                ],'json')->send();
        }else{
            //验证token
           return AuthService::validateToken($payload);
        }
    }

    /**正确响应*/
    public function pushSuccess($data)
    {
        Response::create([
            'status' => true,
            'code'   => 1,
            'data'   => $data
        ],'json')->send();
    }


}