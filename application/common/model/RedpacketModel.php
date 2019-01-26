<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/12
 * Time: 13:31
 */
namespace app\common\model;

use think\Model;
use think\Log;

class RedpacketModel extends Model
{
    protected $name = 'redpacket';

    public static function addData($addData)
    {
        $res = self::insertGetId($addData);
        return $res;
    }

    public static function findData($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }

    public static function setData($condition,$upData)
    {
        $res = self::where($condition)->update($upData);
        return $res;
    }

    public static function findDatas($condition,$findField)
    {
        $res = self::where($condition)->field($findField)->select();
        return $res;
    }

    public static function setFields($condition,$setField)
    {
         self::where($condition)->setField($setField);
    }

    public static function upNum($condition,$type,$upNum)
    {
        if ($type == 1) {
            $res = self::where($condition)->setInc($upNum);
        }else{
            $res = self::where($condition)->setDec($upNum);

        }
        return $res;
    }
}