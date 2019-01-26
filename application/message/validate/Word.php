<?php
/**
 * Created by PhpStorm.
 * User: greatsir
 * Date: 17-7-11
 * Time: 下午3:21
 */

namespace app\message\validate;

use think\Validate;
class Word extends Validate
{
    protected $rule = [
        'title' =>'chs',
        'content' =>'chs'
    ];
}