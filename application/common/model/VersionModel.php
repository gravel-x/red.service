<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/2
 * Time: 14:36
 */

namespace app\common\model;

use think\Model;
class VersionModel extends Model
{
    protected $name = 'version';

    /**
     * @param $sentence 查询条件
     * @return array|false|\PDOStatement|string|Model
     * 返回值包含app_id,自定义
     *          app_aid,小程序appid
     *          app_secret小程序secret
     */
    public static function getEditon($sentence)
    {
        $res = self::where($sentence)->field('app_id,app_aid,app_secret')->find()->toArray();
        return $res;
    }
}