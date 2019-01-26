<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/11
 * Time: 18:08
 */

namespace app\message\model;
use think\Model;

class PresetModel extends Model
{
    protected $name = 'preset';

    //获取预设口令
    public static function getPersetInfo($condition)
    {
       return self::where($condition)->select();
    }

}