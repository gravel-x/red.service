<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/30
 * Time: 19:45
 */

namespace app\common\service;


class CurlService
{
    /**
     * [cUrl cURL(支持HTTP/HTTPS，GET/POST)]
     * @author qiuguanyou

     * @version   V1.0
     * @date      2017-04-12
     * @param     [string]     $url    [请求地址]
     * @param     [Array]      $header [HTTP Request headers array('Content-Type'=>'application/x-www-form-urlencoded')]
     * @param     [Array]      $data   [参数数据 array('name'=>'value')]
     * @return    [type]               [如果服务器返回xml则返回xml，不然则返回json]
     */
    public static function cUrl($url,$header=null, $data = null){
        //初始化curl
        $UserAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.84 Safari/537.36';
        $curl = curl_init();
        //设置cURL传输选项
        if(is_array($header)){
            curl_setopt($curl, CURLOPT_HTTPHEADER  , $header);
        }
        curl_setopt($curl, CURLOPT_USERAGENT, $UserAgent);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){//post方式
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        //获取采集结果
        $output = curl_exec($curl);
        //关闭cURL链接
        curl_close($curl);
        return $output;
    }
}