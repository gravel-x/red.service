<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/3/13
 * Time: 13:10
 */
namespace app\users\service;
use app\common\service\BaseService;
use Payment\Config;

class AccessService extends BaseService
{
    public static function getAccessToken()
    {

        $WXconfig = config('wxpay');
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$WXconfig['app_id'].'&secret=APPSECRET');
        //设置头文件的信息作为数据流输出
        curl_setopt($curl, CURLOPT_HEADER, 1);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        print_r($data);
    }
}