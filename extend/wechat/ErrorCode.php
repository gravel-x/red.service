<?php
/**
 * Created by PhpStorm.
 * User: greatsir
 * Date: 2018/2/5
 * Time: 下午1:38
 */
namespace wechat;

class ErrorCode
{
    public static $OK = 0;
    public static $IllegalAesKey = -41001;
    public static $IllegalIv = -41002;
    public static $IllegalBuffer = -41003;
    public static $DecodeBase64Error = -41004;
}