<?php
/**
 * Created by PhpStorm.
 * User: yueling
 * Date: 2018/4/2
 * Time: 16:47
 */

namespace app\common\model;

use think\Model;

class RelationModel extends Model
{
    protected $name = 'relation';

    /**
     * @param $condition  需要查询的条件
     * @param $findData   需要查询的字段名称
     * @return $res  返回的数据以数组存在
     */
    public static function relationInfo($condition,$findData)
    {
        $res = self::where($condition)->field($findData)->find();
        return $res;
    }

    /**
     * @param $newData 新增的数据，以数组形式存在
     * @return   $res  返回新增的自增长ID
     */
    public static function addRelation(array $newData)
    {
        $res = self::insert($newData);
        return $res;
    }
}