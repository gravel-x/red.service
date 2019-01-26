<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/10
 * Time: 15:45
 */

namespace app\common\model;

use think\Model;

class ReceivedModel extends Model
{
    protected $name = 'received';

    public static function receiveDetail($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->order('create_time desc')->limit(10)->select();
        return $res;
    }

    public static function findData($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }


    public static function getReceiveOrder($condition,$need)
    {
        return self::where($condition)->order($need)->find();

    }

    public static function getReceiveValue($condition,$findData)
    {
        return self::where($condition)->value($findData);
    }

    public static function getReceiveCount($condition)
    {
        return self::where($condition)->count();
    }


    public static function getReceiveInfo($condition)
    {
        return self::where($condition)->select();
    }

    //关联查询user_info表
    public static function getJoin($condition,$field,$limit)
    {
        return self::alias('a')->join('user_info b','a.user_id = b.user_id')->where($condition)
            ->field($field)->order('create_time desc')->limit($limit,5)->select();
    }

    //关联查询redpacket表
    public static function getJoinRedpacket($condition,$page)
    {

        $res=  self::alias('a')->join('redpacket b','a.rp_id = b.rp_id')->where($condition)
            ->field('b.rp_content,b.rp_id,b.rp_type,a.received_voice,a.duration,a.create_time')->order('a.create_time desc')->limit($page,5)->select();

        return $res;
    }

    public static function addData($addData)
    {
        $res = self::insert($addData);
        return $res;
    }

    public static function upData($condition,$upData)
    {
        $res = self::where($condition)->update($upData);
        return $res;
    }

}