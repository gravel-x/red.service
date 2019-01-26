<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/3
 * Time: 13:39
 */
namespace app\common\model;
use think\Model;
use think\Log;
class UserInfoModel extends Model
{
    protected $name = 'user_info';

    public static function getUserInfo($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }

    public static function changData($condition,$changData)
    {
        $res = self::where($condition)->update($changData);
        return $res;
    }

    public static function addEmpty($data)
    {
        $res = self::insertGetId($data);
        return $res;
    }

    public static function addShareTimes($user_id)
    {
        $res = self::where('user_id', $user_id)->setInc('share_times');
        return $res;
    }

}