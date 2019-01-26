<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 10:25
 */

namespace app\redpacket\model;

use think\Model;

class UserModel extends Model
{
    protected $name = 'user';

    //新增问题
    public static function getUserData($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }

    //修改状态值
    public static function setUserDate($condition,$setDate)
    {
        $res = self::where($condition)->setField($setDate);
        return $res;
    }


}