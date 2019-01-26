<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 15:30
 */

namespace app\common\model;

use think\Model;

class SendModel extends Model
{
    protected $name = 'userInfo';

    public static function sendDetail($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->order('create_time desc')->select();
        return $res;
    }
}