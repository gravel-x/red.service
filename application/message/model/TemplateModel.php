<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/16
 * Time: 10:57
 */
namespace app\message\model;
use think\Model;

class TemplateModel extends Model
{
    protected $name = 'template';
    public static function getTemplate($condition,$findData)
    {
        return self::where($condition)->value($findData);

    }

}