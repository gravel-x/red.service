<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/11
 * Time: 16:02
 */
namespace app\common\model;

use think\Model;

class ExtensionModel extends Model
{
    protected $name = 'extension';

    public static function getInfo($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }
}