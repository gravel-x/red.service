<?php
/**
 * Created by PhpStorm.
 * User: greatsir
 * Date: 2018/1/29
 * Time: 下午6:05
 */

namespace app\users\validate;

use think\Validate;

class WxLogin extends Validate
{
    protected $rule = [
        'openid'=>'require',
        'unionid'=>'require'
    ];
}