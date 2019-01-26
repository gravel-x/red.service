<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/11
 * Time: 16:15
 */

namespace app\common\model;

use think\Model;

class ShareModel extends Model
{
    protected $name = 'share';

    public static function getInfo($condition,$time,$findData)
    {
        $res = self::where($condition)->where('create_time','gt',$time)->field($findData)->find();
        return $res;
    }

    public static function addInfo($addData)
    {
        $res = self::insert($addData);
        return $res;
    }
}